<?php
namespace M6Web\Bundle\FirewallBundle\Tests\Units\Firewall;

require_once __DIR__.'/../../bootstrap.php';

use atoum\AtoumBundle\Test\Units;

use M6Web\Bundle\FirewallBundle\Firewall;

/**
 * Firewall manager test
 */
class Provider extends Units\Test
{
    protected $request;

    protected $configs = array(
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
        'empty' => array(
            'toomuch' => true,
        ),
    );

    protected $expectedConfig = array(
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
        'empty' => array(
            'default_state' => null,
            'error_message' => null,
            'error_code' => null,
            'throw_error' => null,
            'lists' => null,
            'entries' => array(),
        ),
    );

    protected $lists = array(
        'default' => array(
            '::1',
            '192.168.1.*',
            '192.168.0.0-192.168.0.254',
        ),
        'partners' => array(
            '10.20.30.40',
            '40.30.20.10',
        ),
    );

    protected $options = array(
        'default_state' => false,
        'error_message' => 'TestOptions',
    );

    /**
     * Instantiation test
     */
    public function testConstruct()
    {
        $provider = $this->getProvider();

        $this->assert
            ->array($provider->getLists())
                ->isEqualTo($this->lists)
            ->array($provider->getConfigs())
                ->isEqualTo($this->expectedConfig)
        ;
    }

    /**
     * Generation test
     */
    public function testGetFirewall()
    {
        $provider = $this->getProvider();
        $provider->setFirewallClass('\mock\M6Web\Bundle\FirewallBundle\Firewall\FirewallInterface');

        $firewall = $provider->getFirewall('default', $this->options);

        $this
            ->mock($firewall)
                ->call('setProvider')
                    ->withIdenticalArguments($provider)
                    ->once()
                ->call('setRequest')
                    ->withIdenticalArguments($this->request)
                    ->once()
                ->call('setDefaultState')
                    ->withIdenticalArguments($this->options['default_state'])
                    ->once()
                ->call('setErrorMessage')
                    ->withIdenticalArguments($this->options['error_message'])
                    ->once()
        ;
    }

    /**
     * Get a provider mock
     *
     * @return Firewall\Provider
     */
    protected function getProvider()
    {
        $container = new \Mock\Symfony\Component\DependencyInjection\ContainerInterface();
        $this->request = new \Mock\Symfony\Component\HttpFoundation\Request();
        $container->getMockController()->get = function($serviceName) {
            switch ($serviceName) {
                case 'request':
                    return $this->request;
            }
        };

        return new Firewall\Provider($container, $this->lists, $this->configs);
    }
}
