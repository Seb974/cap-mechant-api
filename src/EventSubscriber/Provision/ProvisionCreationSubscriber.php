<?php

namespace App\EventSubscriber\Provision;

use App\Entity\Provision;
use App\Service\Stock\StockManager;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\Sms\ProvisionNotifier as SMSNotifier;
use App\Service\Email\ProvisionNotifier as EmailNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProvisionCreationSubscriber implements EventSubscriberInterface 
{
    private $stockManager;
    private $smsNotifier;
    private $emailNotifier;

    public function __construct(SMSNotifier $smsNotifier, EmailNotifier $emailNotifier, StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
        $this->smsNotifier = $smsNotifier;
        $this->emailNotifier = $emailNotifier;
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::VIEW => ['fitOrder', EventPriorities::PRE_WRITE] ];
    }

    public function fitOrder(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $request = $event->getRequest();
        $method = $request->getMethod();

        if ( $result instanceof Provision ) {
            if ( $method === "POST" ) {
                $status = !is_null($result->getStatus()) ? $result->getStatus() : "ORDERED";
                if ($status === "ORDERED") {
                    if (str_contains(strtoupper($result->getSendingMode()), "SMS"))
                        $this->smsNotifier->notifyOrder($result);
                    if (str_contains(strtoupper($result->getSendingMode()), "EMAIL"))
                        $this->emailNotifier->notify($result);
                }
                $result->setStatus($status);
            }
            else if ( $method === "PUT" && $result->getStatus() === "ORDERED" && !$result->getIntegrated() ) {
                $result->setStatus("RECEIVED");
            }
        }
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