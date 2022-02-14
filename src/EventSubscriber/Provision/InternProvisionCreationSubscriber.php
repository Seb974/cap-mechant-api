<?php

namespace App\EventSubscriber\Provision;

use App\Entity\Provision;
use App\Service\Order\DataSender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InternProvisionCreationSubscriber implements EventSubscriberInterface 
{
    private $em;
    private $dataSender;

    public function __construct(DataSender $dataSender, EntityManagerInterface $em)
    {
        $this->dataSender = $dataSender;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::VIEW => ['fitOrder', EventPriorities::POST_WRITE] ];
    }

    public function fitOrder(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $request = $event->getRequest();
        $method = $request->getMethod();

        if ( $result instanceof Provision && in_array($method, ["POST", "PUT"]) ) {

            if ($result->getStatus() == "ORDERED" && (is_null($result->getIntegrated()) || !$result->getIntegrated())) {
                if ($result->getSupplier()->getIsIntern()) {
                    $status = $this->dataSender->sendToVIF($result);
                    $failure = $status === 'failed';
                    $result->setIntegrated(!$failure);
                    $this->em->persist($result);
                }
            }
        }
    }
}