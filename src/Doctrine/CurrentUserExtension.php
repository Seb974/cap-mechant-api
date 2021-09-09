<?php

namespace App\Doctrine;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Provision;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $auth;
    private $requestStack;
    private $publicDomain;

    public function __construct($requestStack, $admin, $public, Security $security, AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
        $this->adminDomain = $admin;
        $this->security = $security;
        $this->publicDomain = $public;
        $this->requestStack = $requestStack;
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
        if ($origin === $this->publicDomain && !$this->auth->isGranted('ROLE_TEAM') && ($user instanceof User ))
        {
            $rootAlias = $queryBuilder->getRootAliases()[0];

            if ($resourceClass == Product::class) {
                $queryBuilder->andWhere(":user MEMBER OF $rootAlias.users")
                             ->setParameter("user", $user);
            }

            if ($resourceClass == Provision::class) {
                $queryBuilder->leftJoin("$rootAlias.user","u")
                             ->andWhere("u IS NOT NULL")
                             ->andWhere("u.id = :user")
                             ->setParameter("user", $user->getId());
            }
        }
    }
}