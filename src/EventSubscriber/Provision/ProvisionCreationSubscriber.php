<?php

namespace App\EventSubscriber\Provision;

use App\Entity\Provision;
use App\Service\Order\DataSender;
use App\Service\Stock\StockManager;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Repository\SellerRepository;
use App\Service\Sms\ProvisionNotifier as SMSNotifier;
use App\Service\Email\ProvisionNotifier as EmailNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProvisionCreationSubscriber implements EventSubscriberInterface 
{
    private $dataSender;
    private $stockManager;
    private $smsNotifier;
    private $emailNotifier;
    private $sellerRepository;

    public function __construct(SMSNotifier $smsNotifier, EmailNotifier $emailNotifier, StockManager $stockManager, DataSender $dataSender, SellerRepository $sellerRepository)
    {
        $this->dataSender = $dataSender;
        $this->stockManager = $stockManager;
        $this->smsNotifier = $smsNotifier;
        $this->emailNotifier = $emailNotifier;
        $this->sellerRepository = $sellerRepository;
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
                $result->setOrderDate(new \DateTime());
                $this->setDefaultSeller($result);
                $this->setInternValue($result);
            }

            if ($result->getStatus() == "ORDERED" && (is_null($result->getIntegrated()) || !$result->getIntegrated())) {
                if (!$result->getSupplier()->getIsIntern()) {
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

    private function setDefaultSeller(Provision $provision)
    {
        if (is_null($provision->getSeller())) {
            $provision->setSeller($this->getDefaultSeller());
        }
    }

    private function setInternValue(Provision $provision)
    {
        if (is_null($provision->getIsIntern())) {
            $provision->setIsIntern($provision->getSupplier()->getIsIntern());
        }
    }

    private function getDefaultSeller()
    {
        return $this->sellerRepository->find(1);
    }
}