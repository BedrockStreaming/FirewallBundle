<?php
namespace M6Web\Bundle\FirewallBundle\Tests\Units\EventListener;

require_once __DIR__.'/../../bootstrap.php';

use atoum\AtoumBundle\Test\Units;

use M6Web\Bundle\FirewallBundle\EventListener\RequestListener as TestedClass;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * Request listener test
 */
class RequestListener extends Units\Test
{

    /**
     * test on request with a request matching pattern
     *
     * @return Request
     */
    public function testOnRequestWithValidRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('/test');

        $mockKernel = new \mock\Symfony\Component\HttpKernel\HttpKernelInterface();

        $event = new \Symfony\Component\HttpKernel\Event\GetResponseEvent($mockKernel, $request, null);

        $mockedProvider = $this->getMockedProvider();

        $requestListener = new TestedClass($mockedProvider);

        $this->if($requestListener)
            ->then($requestListener->onRequest($event))
            ->mock($mockedProvider)
                ->call('getPatterns')
                    ->once()
                ->call('getFirewall')
                    ->once()
                    ->withArguments('configTest');
    }

    /**
     * test on request with a request not matching pattern
     *
     * @return Request
     */
    public function testOnRequestWithUnValidRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('/toto');

        $mockKernel = new \mock\Symfony\Component\HttpKernel\HttpKernelInterface();

        $event = new \Symfony\Component\HttpKernel\Event\GetResponseEvent($mockKernel, $request, null);

        $mockedProvider = $this->getMockedProvider();

        $requestListener = new TestedClass($mockedProvider);

        $this->if($requestListener)
            ->then($requestListener->onRequest($event))
            ->mock($mockedProvider)
                ->call('getPatterns')
                    ->once()
                ->call('getFirewall')
                    ->never();
    }

    /**
     * test on request with no patterns
     *
     * @return Request
     */
    public function testOnRequestWithNoPatterns()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('/toto');

        $mockKernel = new \mock\Symfony\Component\HttpKernel\HttpKernelInterface();

        $event = new \Symfony\Component\HttpKernel\Event\GetResponseEvent($mockKernel, $request, null);

        $mockedProvider = $this->getMockedProvider();

        $mockedProvider->getMockController()->getPatterns = function() {
            return null;
        };

        $requestListener = new TestedClass($mockedProvider);

        $this->if($requestListener)
            ->then($requestListener->onRequest($event))
            ->mock($mockedProvider)
                ->call('getPatterns')
                    ->once()
                ->call('getFirewall')
                    ->never();
    }

    protected function getMockedProvider()
    {
        $firewallProvider = new \mock\M6Web\Bundle\FirewallBundle\Firewall\ProviderInterface();

        $firewallProvider->getMockController()->getPatterns = function() {
            return array(
                'pattern1' => array(
                    'path' => '/test',
                    'config' => 'configTest',
                    'matcher' => new RequestMatcher('/test')
                )
            );
        };

        $firewallProvider->getMockController()->getFirewall = function() {
            return new \mock\M6Web\Bundle\FirewallBundle\Firewall\FirewallInterface();
        };

        return $firewallProvider;
    }
}
