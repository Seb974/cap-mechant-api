<?php

namespace App\EventSubscriber\Order;

use App\Entity\OrderEntity;
use App\Service\Order\DataSender;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderCreatedSubscriber implements EventSubscriberInterface 
{
    protected $dataSender;

    public function __construct(DataSender $dataSender)
    {
        $this->dataSender = $dataSender;
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::VIEW => ['sendToVIF', EventPriorities::POST_WRITE] ];
    }

    public function sendToVIF(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $request = $event->getRequest();
        $method = $request->getMethod();

        if ($method === "POST" && $result instanceof OrderEntity && $result->getStatus() === "WAITING") {
            $this->dataSender->sendToVIF($result);
        }
    }
}