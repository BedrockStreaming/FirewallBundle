<?php

namespace M6Web\Bundle\FirewallBundle\EventListener;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
/**
 * class RequestListener
 */
class RequestListener extends Bundle
{


    /**
    * firewall provider
    */
    protected $provider;

    /**
    * @param provider $provider firewall provider
    */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
    * on request event
    * @param GetResponseEvent $event event
    */
    public function onRequest(GetResponseEvent $event)
    {

        foreach ($this->provider->getPatterns() as $pattern) {

            if ($pattern['matcher']->matches($event->getRequest())) {

               $firewall = $this->provider->getFirewall($pattern['config']);

               $firewall->handle();
            }
        }

    }

}
