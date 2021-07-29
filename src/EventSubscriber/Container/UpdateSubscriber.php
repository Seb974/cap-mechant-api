<?php

namespace App\EventSubscriber\Container;

use App\Entity\Container;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\Axonaut\Container as AxonautContainer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * UpdateSubscriber
 *
 * Informations :
 * When a container is created or updated, this event subscriber set the Axonaut id.
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class UpdateSubscriber implements EventSubscriberInterface 
{
    private $axonaut;

    public function __construct(AxonautContainer $axonaut)
    {
        $this->axonaut = $axonaut;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['updateEntity', EventPriorities::PRE_WRITE]
        ];
    }

    public function updateEntity(ViewEvent $event)
    {
        // $result = $event->getControllerResult();
        // $method = $event->getRequest()->getMethod();

        // if ($result instanceof Container) {
        //     if ($method === "POST") {
        //         $accountingId = $this->axonaut->createContainer($result);
        //         $result->setAccountingId($accountingId);
        //     } else if ($method === "PUT" || $method === "PATCH") {
        //         $accountingId = $this->axonaut->updateContainer($result);
        //         $result->setAccountingId($accountingId);
        //     }
        // }
    }
}