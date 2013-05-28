<?php
namespace M6Web\Bundle\FirewallBundle\Tests\Units\DependencyInjection;

require_once __DIR__.'/../../bootstrap.php';

use atoum\AtoumBundle\Test\Units;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use M6Web\Bundle\FirewallBundle\DependencyInjection\FirewallExtension as FirewallExt;

/**
 * Test of the FirewallBundle extension declaration
 */
class FirewallExtension extends Units\Test
{
    protected $configs = array(
        array(
           'configs' => array(
                'default' => array(
                    'default_state' => true,
                    'error_code'    => 400,
                    'error_message' => 'Interdit',
                    'throw_error'   => false,
                ),
            ),
            'lists' => array(
                'partners' => array(
                    '10.20.30.40',
                    '40.30.20.10',
                ),
            ),
        ),
        array(
            'configs' => array(
                'default' => array(
                    'lists'   => array(
                        'default'  => true,
                        'partners' => false,
                    ),
                    'entries' => array(
                        '127.0.0.1'   => true,
                        '192.168.1.1' => false,
                    ),
                ),
            ),
            'lists' => array(
                'default' => array(
                    '::1',
                    '192.168.1.*',
                    '192.168.0.0-192.168.0.254',
                ),
            ),
        )
    );

    /**
     * Configuration load test
     */
    public function testLoad()
    {
        $container = new ContainerBuilder();

        $loader = new FirewallExt();
        $loader->load($this->configs, $container);

        $this->assert
            ->string($container->getParameter('m6web.firewall.class'))
                ->isEqualTo('M6Web\Bundle\FirewallBundle\Firewall\Firewall')
            ->array($container->getParameter('m6web.firewall.lists'))
                ->isEqualTo(array(
                    'default' => array(
                        '::1',
                        '192.168.1.*',
                        '192.168.0.0-192.168.0.254',
                    ),
                    'partners' => array(
                        '10.20.30.40',
                        '40.30.20.10',
                    ),
                ))
            ->array($container->getParameter('m6web.firewall.configs'))
                ->isEqualTo(array(
                    'default' => array(
                        'default_state' => true,
                        'error_code'    => 400,
                        'error_message' => 'Interdit',
                        'throw_error'   => false,
                        'lists'   => array(
                            'default'  => true,
                            'partners' => false,
                        ),
                        'entries' => array(
                            '127.0.0.1'   => true,
                            '192.168.1.1' => false,
                        ),
                    ),
                ))

            ->object($providerDefinition = $container->getDefinition('m6web.firewall.provider'))
            ->string($providerDefinition->getClass())
                ->isEqualTo('M6Web\Bundle\FirewallBundle\Firewall\Provider')
            ->object($providerDefinition->getArgument(0))
                ->isInstanceOf('Symfony\Component\DependencyInjection\Reference')
            ->string((string) $providerDefinition->getArgument(0))
                ->isEqualTo('service_container')
            ->string($providerDefinition->getArgument(1))
                ->isEqualTo('%m6web.firewall.lists%')
            ->string($providerDefinition->getArgument(2))
                ->isEqualTo('%m6web.firewall.configs%')
            ->array($providerDefinition->getMethodCalls())
                ->hasSize(1)
            ->array($providerDefinition->getMethodCalls()[0])
                ->isEqualTo(array('setFirewallClass', array('%m6web.firewall.class%')))

            ->object($listenerDefinition = $container->getDefinition('m6web.firewall.controller_listener'))
            ->string($listenerDefinition->getClass())
                ->isEqualTo('M6Web\Bundle\FirewallBundle\Controller\Listener')
            ->object($listenerDefinition->getArgument(0))
                ->isInstanceOf('Symfony\Component\DependencyInjection\Reference')
            ->string((string) $listenerDefinition->getArgument(0))
                ->isEqualTo('annotation_reader')
            ->object($listenerDefinition->getArgument(1))
                ->isInstanceOf('Symfony\Component\DependencyInjection\Reference')
            ->string((string) $listenerDefinition->getArgument(1))
                ->isEqualTo('m6web.firewall.provider')
            ->array($listenerDefinition->getTag('kernel.event_listener'))
                ->isEqualTo(array(array('event' => 'kernel.controller', 'method' => 'onCoreController', 'priority' => -255)))
            ;
    }
}
