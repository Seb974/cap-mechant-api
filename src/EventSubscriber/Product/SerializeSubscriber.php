<?php

namespace App\EventSubscriber\Product;

use App\Entity\Product;
use App\Service\Product\PriceFilter;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\Common\Collections\ArrayCollection;
use PhpParser\Node\Expr\Instanceof_;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SerializerSubscriber
 *
 * Informations :
 * When a GET request for Product entity is sent, this eventSusbcriber 
 * filter the prices to only return the price defined for the group that
 * the user belong to. 
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class SerializeSubscriber implements EventSubscriberInterface
{
    private $admin;
    private $server;
    private $priceFilter;

    public function __construct($admin, $server, PriceFilter $priceFilter)
    {
        $this->admin = $admin;
        $this->server = $server;
        $this->priceFilter = $priceFilter;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['filterPrices', EventPriorities::POST_SERIALIZE]
        ];
    }

    public function filterPrices(ViewEvent $event)
    {
        // $result = json_decode($event->getControllerResult(), true);
        // $method = $event->getRequest()->getMethod();
        // $origin = $event->getRequest()->headers->get("origin");

        // if ($origin != $this->admin && $origin != $this->server && $method == "GET" && (strpos(strtoupper($result['@type']), "PRODUCT") !== false || 
        //    (strpos(strtoupper($result['@type']), "COLLECTION") !== false && count($result['hydra:member']) > 0 && 
        //     strpos(strtoupper($result['hydra:member'][0]['@type']), "PRODUCT") !== false)) )
        // {
        //     $response = $this->priceFilter->filter($result);
        //     $event->setControllerResult(json_encode($response));
        // }
    }
}