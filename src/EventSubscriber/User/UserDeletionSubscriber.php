<?php

namespace App\EventSubscriber\User;

use App\Entity\User;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\Axonaut\User as AxonautUser;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserDeletionSubscriber implements EventSubscriberInterface
{
    private $axonaut;

    public function __construct(AxonautUser $axonaut)
    {
        $this->axonaut = $axonaut;
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::REQUEST => ['removeUserFromCustomers', EventPriorities::POST_READ] ];
    }

    public function removeUserFromCustomers(RequestEvent $event)
    {
        // $method = $event->getRequest()->getMethod();
        // $previous = $event->getRequest()->attributes->get('previous_data');

        // if ($previous instanceof User && $method === "DELETE") {
        //     $this->axonaut->removeFromCustomers($previous);
        // }
    }
}
