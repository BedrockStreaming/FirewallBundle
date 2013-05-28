<?php
namespace M6Web\Bundle\FirewallBundle\Tests\Units\Controller;

require_once __DIR__.'/../../bootstrap.php';

use atoum\AtoumBundle\Test\Units;

use M6Web\Bundle\FirewallBundle\Controller,
    M6Web\Bundle\FirewallBundle\Firewall\Provider;

/**
 * Controller listener test
 */
class Listener extends Units\Test
{
    /**
     * Instantiation test
     *
     * @return Request
     */
    public function testConstruct()
    {
        $this->mockClass('Symfony\Component\DependencyInjection\ContainerInterface', '\Mock');
        $this->mockClass('Symfony\Component\HttpFoundation\Request', '\Mock');
        $this->mockClass('Doctrine\Common\Annotations\AnnotationReader', '\Mock');

        $request   = new \Mock\Request();
        $container = new \Mock\ContainerInterface();

        $container->getMockController()->get = function($serviceName) use ($request) {
            switch ($serviceName) {
                case 'request':
                    return $request;
            }
        };

        $provider = new Provider($container, array(), array());
        $reader   = new \Mock\AnnotationReader();

        $listener = new Controller\Listener($reader, $provider);

        $this->assert
            ->object($provider)
                ->isIdenticalTo($listener->getProvider())
        ;

        $this->assert
            ->object($reader)
                ->isIdenticalTo($listener->getReader())
        ;
    }
}
