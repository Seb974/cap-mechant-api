<?php

namespace App\Doctrine;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Seller;
use App\Entity\Touring;
use App\Entity\Product;
use App\Entity\OrderEntity;
use Doctrine\ORM\QueryBuilder;
use App\Service\User\UserGroupDefiner;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use App\Entity\Provision;
use App\Entity\Relaypoint;
use App\Entity\Supplier;
use App\Repository\SupervisorRepository;

class CurrentAdminExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $auth;
    private $security;
    private $adminDomain;
    private $requestStack;
    private $userGroupDefiner;
    private $supervisorRepository;

    public function __construct($requestStack, $admin, $public, Security $security, AuthorizationCheckerInterface $auth, UserGroupDefiner $userGroupDefiner, SupervisorRepository $supervisorRepository)
    {
        $this->auth = $auth;
        $this->adminDomain = $admin;
        $this->security = $security;
        $this->publicDomain = $public;
        $this->requestStack = $requestStack;
        $this->userGroupDefiner = $userGroupDefiner;
        $this->supervisorRepository = $supervisorRepository;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?string $operationName = null)
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass)
    {
        $user = $this->security->getUser();
        $request = $this->requestStack->getCurrentRequest();
        $origin = $request->headers->get('origin');

        if ($origin === $this->adminDomain && !$this->auth->isGranted('ROLE_SELLER') && !$this->auth->isGranted('ROLE_PICKER') && !$this->auth->isGranted('ROLE_SUPERVISOR') && $user instanceof User)
        {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $userGroup = $this->userGroupDefiner->getUserGroup($user);

            if ( $resourceClass == Product::class ) {
                $queryBuilder->leftJoin("$rootAlias.seller","s")
                             ->leftJoin("s.users", "u")
                             ->andWhere("u IS NOT NULL")
                             ->andWhere(":user = u.id")
                             ->setParameter("user", $user->getId());
            }

            if ( $resourceClass == Seller::class ) {
                $queryBuilder->andWhere(":user MEMBER OF $rootAlias.users")
                             ->setParameter("user", $user);
            }

            if ( $resourceClass == Supplier::class ) {
                $queryBuilder->leftJoin("$rootAlias.seller","s")
                             ->andWhere(":user MEMBER OF s.users")
                             ->setParameter("user", $user);
            }

            if ( $resourceClass == Provision::class ) {
                $queryBuilder->leftJoin("$rootAlias.seller","s")
                             ->andWhere(":user MEMBER OF s.users")
                             ->setParameter("user", $user);
            }

            if ( $resourceClass == Touring::class ) {
                $queryBuilder->leftJoin("$rootAlias.deliverer","u")
                             ->andWhere("u IS NOT NULL")
                             ->andWhere("u.id = :user")
                             ->setParameter("user", $user);
            }

            if ( $resourceClass == Relaypoint::class ) {
                $queryBuilder->andWhere(":user MEMBER OF $rootAlias.managers")
                             ->setParameter("user", $user);
            }
        }

        if ($origin === $this->adminDomain && $this->auth->isGranted('ROLE_SUPERVISOR') && !$this->auth->isGranted('ROLE_ADMIN') && $user instanceof User)
        {
            $arrayId = [];
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $supervisor = $this->supervisorRepository->findByUser($user);

            foreach ($supervisor->getUsers() as $user) {
                $arrayId[] = $user->getId();
            }

            if ( $resourceClass == User::class && !is_null($supervisor) ) {
                $queryBuilder->andWhere("$rootAlias.id IN (:ids)")
                             ->setParameter("ids", $arrayId);
            }

            if ($resourceClass == OrderEntity::class && !is_null($supervisor)) {
                $queryBuilder->leftJoin("$rootAlias.user","u")
                             ->andWhere("u IS NOT NULL")
                             ->andWhere("u.id IN (:ids)")
                             ->setParameter("ids", $arrayId);
            }
        }
    }
}