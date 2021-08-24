<?php

namespace App\Service\JwtAuthentication;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * CookieGenerator
 *
 * Informations :
 * This service create with its unique method "generate", the BEARER cookie, 
 * used for authentication with API PLATFORM.
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class CookieGenerator
{
    private $tokenTTL;
    private $cookieDomain;
    
    public function __construct(string $tokenTTL, string $cookieDomain)
    {
        $this->tokenTTL = $tokenTTL;
        $this->cookieDomain = $cookieDomain;
    }

    public function generate($token) : Cookie 
    {
        $expire = (new \DateTime())->add(new \DateInterval('PT' . $this->tokenTTL . 'S'));
        return Cookie::create('BEARER')
                    ->withValue($token)
                    ->withDomain($this->cookieDomain)
                    ->withPath('/')
                    ->withSecure(false)
                    ->withHttpOnly(true)
                    ->withSameSite('lax')
                    ->withExpires($expire);
    }
}