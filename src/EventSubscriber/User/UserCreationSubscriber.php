<?php

namespace App\EventSubscriber\User;

use App\Entity\Meta;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\Axonaut\User as AxonautUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * UserCreationSubscriber
 *
 * Informations :
 * When a user is created with POST method (register or using admin panel) or updated with PUT method, 
 * this eventSusbcriber hashes the plaintext password sent and add empty metadatas 
 * (represented by Meta entity) if metas aren't sent.
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class UserCreationSubscriber implements EventSubscriberInterface 
{
    private $em;
    private $encoder;
    private $axonaut;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, AxonautUser $axonaut)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->axonaut = $axonaut;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['fitDatas', EventPriorities::PRE_WRITE]
        ];
    }

    public function fitDatas(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($result instanceof User && ($method === "POST" || $method === "PUT")) {
            if ($result->getPassword() !== null && ($method === "POST" || ($method === "PUT" && !str_contains($result->getPassword(), 'argon2')))) {
                $hash = $this->encoder->encodePassword($result, $result->getPassword());
                $result->setPassword($hash);
            }
            if ($method === "POST") {
                if (is_null($result->getMetas())) {
                    $meta = new Meta();
                    $this->em->persist($meta);
                    $result->setMetas($meta);
                }
                // if (is_null($result->getAccountingId())) {
                //     $accountingId = $this->axonaut->createUser($result);
                //     $result->setAccountingId($accountingId);
                // }
                // if (is_null($result->getBillingDetails())) {
                //     $result->setBillingDetails(true);
                // }
            } 
            // else {
            //     $accountingId = $this->axonaut->updateUser($result);
            //     $result->setAccountingId($accountingId);
            // }
        }
    }
}