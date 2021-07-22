<?php

namespace App\EventSubscriber\Response;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ResponseSubscriber
 *
 * Informations :
 * Add headers to each response sent by the API :
 *      - ACCESS-CONTROL-ALLOW-ORIGINS -> To allow clients consuming the API
 *      - ACCESS-CONTROL-ALLOW-CREDENTIALS -> To allow clients storing the authentication cookies
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class ResponseSubscriber implements EventSubscriberInterface
{
    private $apiDomain;
    private $adminDomain;
    private $publicDomain;

    public function __construct(string $server, string $admin, string $public)
    {
        $this->apiDomain = $server;
        $this->adminDomain = $admin;
        $this->publicDomain = $public;
    }

    /** @inheritdoc */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onResponse'
        ];
    }

    /**
     * Callback function for event subscriber
     * @param FilterResponseEvent $event
     */
    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        
        $origin = $request->headers->get('origin');
        $allowedOrigin = $origin == $this->adminDomain ? $this->adminDomain :
                        ($origin == $this->publicDomain ? $this->publicDomain : $this->apiDomain);
        $response->headers->set("Access-Control-Allow-Credentials", 'true');
        $response->headers->set("Access-Control-Allow-Origin", $allowedOrigin);
        $response->headers->set("Access-Control-Allow-Headers", "Content-Type, Authorization, Accept, Cookie, Set-Cookie");
        
        // $response->headers->set("X-Frame-Options", 'Deny');
        // $response->headers->set("X-XSS-Protection", 1);
        // $response->headers->set("X-Content-Type-Options", 'nosniff');
        // $response->headers->set("Referrer-Policy", 'same-origin');
        // $response->headers->set("Strict-Transport-Security", "max-age=63072000; includeSubDomains; preload");
    }
}