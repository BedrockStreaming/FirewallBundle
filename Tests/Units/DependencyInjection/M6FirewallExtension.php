<?php
namespace M6Web\Bundle\FirewallBundle\Tests\Units\DependencyInjection;

require_once __DIR__.'/../../bootstrap.php';

use atoum\AtoumBundle\Test\Units;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use M6Web\Bundle\FirewallBundle\DependencyInjection\FirewallExtension as FirewallExt,
    M6Web\Bundle\FirewallBundle\Tests\Units\Firewall\Provider as ProviderTest;

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

        $configs = $container->getParameter('m6.firewall.configs');
        $lists = $container->getParameter('m6.firewall.lists');

        ProviderTest::assertListsMatch($this, $lists);
        ProviderTest::assertConfigsMatch($this, $configs);
    }
}
