<?php

namespace App\EventSubscriber\Order;

use App\Entity\OrderEntity;
use App\Service\Order\Constructor;
use App\Service\Stock\StockManager;
use App\Service\User\UserGroupDefiner;
use App\Service\User\UserOrderCounter;
use App\Service\Product\ProductSalesCounter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Service\Promotion\PromotionUseCounter;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\Chronopost\Chronopost;
use App\Service\Order\DataSender;
use App\Service\Seller\SellerAccount;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderCreationSubscriber implements EventSubscriberInterface 
{
    private $security;
    private $chronopost;
    private $dataSender;
    private $constructor;
    private $userGroupDefiner;
    private $adminDomain;
    private $publicDomain;
    private $stockManager;
    private $sellerAccount;
    private $userOrderCounter;
    private $productSalesCounter;
    private $promotionUseCounter;

    public function __construct($admin, $public, Constructor $constructor, Security $security, UserGroupDefiner $userGroupDefiner, UserOrderCounter $userOrderCounter, ProductSalesCounter $productSalesCounter, StockManager $stockManager, PromotionUseCounter $promotionUseCounter, SellerAccount $sellerAccount, Chronopost $chronopost, DataSender $dataSender)
    {
        $this->adminDomain = $admin;
        $this->security = $security;
        $this->publicDomain = $public;
        $this->chronopost = $chronopost;
        $this->dataSender = $dataSender;
        $this->constructor = $constructor;
        $this->stockManager = $stockManager;
        $this->sellerAccount = $sellerAccount;
        $this->userOrderCounter = $userOrderCounter;
        $this->userGroupDefiner = $userGroupDefiner;
        $this->productSalesCounter = $productSalesCounter;
        $this->promotionUseCounter = $promotionUseCounter;
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
        $origin = $request->headers->get('origin');
        $user = $this->security->getUser();
        $userGroup = $this->userGroupDefiner->getShopGroup($user);

        if ( $result instanceof OrderEntity ) {
            if ( $origin === $this->publicDomain )
                $this->publicActions($request, $method, $userGroup, $result);
            else if ( $origin === $this->adminDomain )
                $this->adminActions($method, $result);
        }
    }

    private function publicActions($request, $method, $userGroup, $order)
    {
        if ( $method === "POST" ) {
            $this->constructor->adjustOrder($order);
        } else if ( $method === "PUT" ) {
            if (!$userGroup->getOnlinePayment() || ($userGroup->getOnlinePayment() && $this->isCurrentUser($order->getPaymentId(), $request)) )
                throw new \Exception();

            $order->setStatus("WAITING");
        }
        if (($method === "POST" || $method === "PUT") && $order->getStatus() === "WAITING") {
            $this->updateEntitiesCounters($order);
            if ($order->getCatalog()->getNeedsParcel())
                $this->chronopost->setReservationNumbers($order);
        }
    }

    private function adminActions($method, $order)
    {
        if ( $method === "POST" && $order->getStatus() === "WAITING" ) {
            $this->constructor->adjustAdminOrder($order);
            $this->updateEntitiesCounters($order);
            if ($order->getCatalog()->getNeedsParcel())
                $this->chronopost->setReservationNumbers($order);
        } else if ( $method === "PUT" ) {
            if ( in_array($order->getStatus(), ["WAITING", "PRE-PREPARED"]) )
                $this->constructor->adjustPreparation($order);
            else if ( in_array($order->getStatus(), ["COLLECTABLE", "SHIPPED", "DELIVERED"]) ) {
                if (is_null($order->getRegulated()) || !$order->getRegulated())
                    $this->constructor->adjustDelivery($order);
                else
                    $this->sellerAccount->dispatchTurnover($order, "DECREASE");
            }
        }
    }

    private function updateEntitiesCounters($order)
    {
        $this->userOrderCounter->increase($order);
        $this->productSalesCounter->increaseAll($order);
        $this->promotionUseCounter->increase($order);
        $this->stockManager->decreaseOrder($order);
    }

    private function isCurrentUser($uuid, $request)
    {
        $userUuid = $request->query->get('id');
        $pattern = "/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}/";
        if ($uuid === null || preg_match($pattern, $uuid) === 0 || preg_match($pattern, $uuid) === false)
            return false;
        return $userUuid == $uuid;
    }
}