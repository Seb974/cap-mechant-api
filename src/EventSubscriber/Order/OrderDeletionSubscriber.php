<?php

namespace App\EventSubscriber\Order;

use App\Entity\OrderEntity;
use App\Service\Chronopost\Chronopost;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\Event\RequestEvent;
// use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderDeletionSubscriber // implements EventSubscriberInterface
{
    private $chronopost;

    public function __construct(Chronopost $chronopost)
    {
        $this->chronopost = $chronopost;
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::REQUEST => ['deleteSkybillsIfExist', EventPriorities::POST_READ] ];
    }

    public function deleteSkybillsIfExist(RequestEvent $event)
    {
        // $method = $event->getRequest()->getMethod();
        // $previous = $event->getRequest()->attributes->get('previous_data');

        // if ($previous instanceof OrderEntity && !is_null($previous->getReservationNumber()) && $method === "DELETE") {
        //     dump('In delete condition in KernelRequest event');
        //     $this->chronopost->cancelSkybill($previous);
        //     dump("deletion ended and cancel skybill opered");
        // }
    }
}
