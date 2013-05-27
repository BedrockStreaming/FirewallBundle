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
    protected static $request;
    protected static $container;
    protected static $provider;

    public static $configs = array(
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
    );

    public static $lists = array(
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

    protected static $options = array(
        'default_state' => false,
        'error_message' => 'TestOptions',
    );

    /**
     * Instantiation test
     */
    public function testConstruct()
    {
        $provider = $this->getProvider();

        self::assertListsMatch($this, $provider->getLists());
        self::assertConfigsMatch($this, $provider->getConfigs());
    }

    /**
     * Generation test
     */
    public function testGetFirewall()
    {
        $provider = $this->getProvider();
        $provider->setFirewallClass('\mock\M6Web\Bundle\FirewallBundle\Firewall\FirewallInterface');

        $firewall = $provider->getFirewall('default', self::$options);

        $this
            ->mock($firewall)
                ->call('setProvider')
                    ->withIdenticalArguments($provider)
                    ->once()
                ->call('setRequest')
                    ->withIdenticalArguments($this->getRequest())
                    ->once()
                ->call('setDefaultState')
                    ->withIdenticalArguments(self::$options['default_state'])
                    ->once()
                ->call('setErrorMessage')
                    ->withIdenticalArguments(self::$options['error_message'])
                    ->once()
        ;
    }

    /**
     * Get a request mock
     *
     * @return \Mock\Request
     */
    public function getRequest()
    {
        if (!self::$request) {
            $this->mockClass('Symfony\Component\HttpFoundation\Request', '\Mock');

            self::$request = new \Mock\Request();
        }

        return self::$request;
    }

    /**
     * Get a container mock
     *
     * @return \Mock\ContainerInterface
     */
    public function getContainer()
    {
        if (!self::$container) {
            $this->mockClass('Symfony\Component\DependencyInjection\ContainerInterface', '\Mock');

            self::$container = new \Mock\ContainerInterface();
        }

        return self::$container;
    }

    /**
     * Get a provider mock
     *
     * @return \Mock\Provider
     */
    protected function getProvider()
    {
        if (!self::$provider) {
            $container = $this->getContainer();
            $request = $this->getRequest();
            $container->getMockController()->get = function($serviceName) use ($request) {
                switch ($serviceName) {
                    case 'request':
                        return $request;
                }
            };
            self::$provider = new Firewall\Provider($container, self::$lists, self::$configs);
        }

        return self::$provider;
    }

    /**
     * Static assertion of lists matching
     *
     * @param Units\Test $class Test instance
     * @param array      $lists Lists to test
     */
    public static function assertListsMatch(Units\Test $class, $lists)
    {
        $class->assert
            ->array(self::$lists)
                ->isEqualTo($lists)
        ;
    }

    /**
     * Static assertion of configuration matching
     *
     * @param Units\Test $class   Test instance
     * @param array      $configs Configurations to test
     */
    public static function assertConfigsMatch(Units\Test $class, $configs)
    {
        foreach (self::$configs as $configName => $params) {
            foreach ($params as $paramName => $param) {
                $class->assert
                    ->variable($param)
                        ->isEqualTo($configs[$configName][$paramName])
                ;
            }
        }
    }
}
