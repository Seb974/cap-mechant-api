<?php

namespace App\Service\Mercure;

use App\Entity\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Security;

/**
 * CookieGenerator
 *
 * Informations :
 * This service create with its unique public method "generate", the MercureAuthorization cookie.
 * It contains the privates channels that the user given as unique parameter of the function 
 * is subscribed to. 
 * 
 * The subscribed channels selection take care about the user's status (logged in or not) and 
 * the roles is granted to.
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class CookieGenerator
{
    private $key;
    private $path;
    private $config;
    private $domain;
    private $security;
    private $tokenTTL;
    private $cookieDomain;
    
    public function __construct(string $key, string $domain, string $path, string $tokenTTL, string $cookieDomain, Security $security)
    {
        $this->key = $key;
        $this->path = $path;
        $this->domain = $domain;
        $this->tokenTTL = $tokenTTL;
        $this->security = $security;
        $this->cookieDomain = $cookieDomain;
        $this->config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->key));
    }

    public function generate(User $user = null) : Cookie 
    {
        $expiresDate = (new \DateTime())->add(new \DateInterval('PT' . $this->tokenTTL . 'S'));

        $token = $this->config->builder()
                      ->withClaim('mercure', ['subscribe' => $this->getChannels($user)])
                      ->getToken(new Sha256(), $this->config->signingKey())
                      ->toString();

        return Cookie::create('mercureAuthorization')
                      ->withValue($token)
                      ->withDomain($this->cookieDomain)
                      ->withPath($this->path)
                      ->withSecure(false)
                      ->withHttpOnly(true)
                      ->withSameSite('lax')
                      ->withExpires($expiresDate);
    }

    private function getChannels(User $user = null) : array
    {
        $id = $user != null ? $user->getId() : 0;
        $teamPrivateChannels = [
            $this->domain . "/api/users/{id}",                      // users updates
            $this->domain . "/api/users/{id}/metas",                // metas users updates
            $this->domain . "/api/users/{id}/shipments",            // shipments updates
            $this->domain . "/api/provisions/{id}",                 // provisions updates
            $this->domain . "/api/private",                         // general updates (unused)
        ];
        $selfPrivateChannels = [
            $this->domain . "/api/users/" . $id,                    // self updates
            $this->domain . "/api/users/" . $id . "/metas",         // self metas updates
            $this->domain . "/api/users/" . $id . "/shipments",     // shipments updates
        ];
        return $user == null ? [] : 
            ($this->security->isGranted("ROLE_TEAM") ? $teamPrivateChannels : $selfPrivateChannels);
    }
}