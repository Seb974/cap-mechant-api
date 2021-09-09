<?php

namespace App\EventSubscriber\Provision;

use App\Entity\Provision;
use App\Service\Order\DataSender;
use App\Service\Stock\StockManager;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\Sms\ProvisionNotifier as SMSNotifier;
use App\Service\Email\ProvisionNotifier as EmailNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProvisionCreationSubscriber implements EventSubscriberInterface 
{
    private $dataSender;
    private $stockManager;
    private $smsNotifier;
    private $emailNotifier;

    public function __construct(SMSNotifier $smsNotifier, EmailNotifier $emailNotifier, StockManager $stockManager, DataSender $dataSender)
    {
        $this->dataSender = $dataSender;
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
        $smsStatus = '';
        $emailStatus = '';

        if ( $result instanceof Provision && in_array($method, ["POST", "PUT"]) ) {
            if ( $method == "POST" ) {
                $status = !is_null($result->getStatus()) ? $result->getStatus() : "ORDERED";
                $result->setStatus($status);
            }

            if ($result->getStatus() == "ORDERED" && (is_null($result->getIntegrated()) || !$result->getIntegrated())) {
                if ($result->getSupplier()->getIsIntern()) {
                    $status = $this->dataSender->sendToVIF($result);
                    $failure = $status === 'failed';
                    $result->setIntegrated(!$failure);
                } else {
                    if (str_contains(strtoupper($result->getSendingMode()), "SMS"))
                        $smsStatus = $this->smsNotifier->notifyOrder($result);
                    if (str_contains(strtoupper($result->getSendingMode()), "EMAIL"))
                        $emailStatus = $this->emailNotifier->notify($result);
                    $failure = $smsStatus === 'failed' || $emailStatus === 'failed';
                    $result->setIntegrated(!$failure);
                }
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