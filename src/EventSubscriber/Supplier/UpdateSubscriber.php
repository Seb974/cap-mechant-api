<?php

namespace App\EventSubscriber\Supplier;

use App\Entity\Supplier;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\Axonaut\Supplier as AxonautSupplier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * UpdateSubscriber
 *
 * Informations :
 * When a supplier is created or updated, this event subscriber set the Axonaut id.
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class UpdateSubscriber implements EventSubscriberInterface
{
    private $axonaut;

    public function __construct(AxonautSupplier $axonaut)
    {
        $this->axonaut = $axonaut;
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::VIEW => ['updateEntity', EventPriorities::PRE_WRITE] ];
    }

    public function updateEntity(ViewEvent $event)
    {
        // $result = $event->getControllerResult();
        // $method = $event->getRequest()->getMethod();

        // if ($result instanceof Supplier) {
        //     if ($method === "POST") {
        //         $ids = $this->axonaut->createSupplier($result);
        //         $result->setAccountingId($ids['supplier_id'])
        //                ->setAccountingCompanyId($ids['company_id']);
        //     }
        //     else if ($method === "PUT" || $method === "PATCH") {
        //         $accountingId = $this->axonaut->updateSupplier($result);
        //         $result->setAccountingCompanyId($accountingId);
        //     }
        // }
    }
}