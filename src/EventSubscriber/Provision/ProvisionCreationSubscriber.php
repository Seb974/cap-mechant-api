<?php

namespace App\EventSubscriber\Provision;

use App\Entity\Provision;
use App\Service\Axonaut\Expense;
use App\Service\Stock\StockManager;
use App\Service\Sms\ProvisionNotifier;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProvisionCreationSubscriber implements EventSubscriberInterface 
{
    private $axonaut;
    private $stockManager;
    private $provisionNotifier;

    public function __construct(ProvisionNotifier $provisionNotifier, StockManager $stockManager, Expense $axonaut)
    {
        $this->axonaut = $axonaut;
        $this->stockManager = $stockManager;
        $this->provisionNotifier = $provisionNotifier;
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::VIEW => ['fitOrder', EventPriorities::PRE_WRITE] ];
    }

    public function fitOrder(ViewEvent $event)
    {
        // $result = $event->getControllerResult();
        // $request = $event->getRequest();
        // $method = $request->getMethod();

        // if ( $result instanceof Provision ) {
        //     if ( $method === "POST" ) {
        //         $status = !is_null($result->getStatus()) ? $result->getStatus() : "ORDERED";
        //         if ($status === "ORDERED")
        //             $this->provisionNotifier->notifyOrder($result);

        //         $result->setStatus($status)
        //                ->setIntegrated(false);
        //     }
        //     else if ( $method === "PUT" && $result->getStatus() === "ORDERED" && !$result->getIntegrated() ) {
        //         $this->integrateProvision($result);
        //         $this->axonaut->createExpense($result);
        //         $result->setStatus("RECEIVED")
        //                ->setIntegrated(true);
        //     }
        // }
    }

    private function integrateProvision($provision)
    {
        foreach ($provision->getGoods() as $good) {
            $product = $good->getProduct();
            $product->setLastCost($good->getPrice());
            $this->stockManager->addToStock($good);
        }
    }
}