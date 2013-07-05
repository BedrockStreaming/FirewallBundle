<?php
namespace M6Web\Bundle\FirewallBundle\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * Firewall manager
 * Get a default firewall instance or with a predefined configuration
 *
 * @author Jérémy Jourdin <jjourdin.externe@m6.fr>
 */
class Provider implements ProviderInterface
{
    /**
     * @var array|null $lists Lists of predefined named ip
     */
    protected $lists;

    /**
     * @var array|null $configs Predefined configurations
     */
    protected $configs;

    /**
     * @var array|null $configs Predefined patterns
     */
    protected $patterns;

    /**
     * @var string $firewallClass Class for firewall objects
     */
    protected $firewallClass = 'M6Web\Bundle\FirewallBundle\Firewall\Firewall';

    /**
     * @var array $configModel Configuration model use to format configuration
     */
    protected $configModel = array(
        "default_state" => array(
            "default" => null,
            "method"  => "setDefaultState",
        ),
        "error_message" => array(
            "default" => null,
            "method"  =>"setErrorMessage",
        ),
        "error_code" => array(
            "default" => null,
            "method"  =>"setErrorCode",
        ),
        "throw_error" => array(
            "default" => null,
            "method"  =>"setThrowError",
        ),
        "lists" => array(
            "default" => null,
        ),
        "entries" => array(
            "default" => array(),
        ),
    );

    /**
     * @var array $firewalls Activated firewalls
     */
    protected $firewalls = array();

    /**
     * @var ContainerInterface $container Service container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container Service container
     * @param array              $lists     Lists of predefined ip
     * @param array              $configs   Predefined configurations
     */
    public function __construct(ContainerInterface $container, array $lists = null, array $configs = null, $patterns = null)
    {
        $this->lists        = $lists;
        $this->container    = $container;

        if (!empty($configs)) {
            foreach ($configs as $configName => $config) {
                $this->loadConfig($configName, $config);
            }
        }

        if (!empty($patterns)) {
            foreach ($patterns as $patternName => $pattern) {
                $this->loadPattern($patternName, $pattern);
            }
        }
    }

    /**
     * Load a formated configuration
     *
     * @param string $configName Configuration name in $this->configs
     * @param array  $config     Configuration data
     *
     * @return $this
     */
    protected function loadConfig($configName, array $config)
    {
        $this->configs[$configName] = $this->normalizeConfig($config);

        return $this;
    }

    /**
     * Load a formated pattern
     *
     * @param string $patternName Patern  name in $this->patterns
     * @param array  $pattern     Pattern data
     *
     * @return $this
     */
    protected function loadPattern($patternName, array $pattern)
    {
        $pattern['matcher'] = new RequestMatcher($pattern['path']);

        $this->patterns[$patternName] = $pattern;

        return $this;
    }

    /**
     * Format an array of configurations with the model
     *
     * @param array $config Base configurations
     *
     * @return array Formated configurations
     */
    protected function normalizeConfig(array $config)
    {
        foreach ($this->configModel as $elmt => $settings) {
            if (!isset($config[$elmt])) {
                $config[$elmt] = $settings['default'];
            }
        }

        foreach ($config as $elmtName => $value) {
            if (!isset($this->configModel[$elmtName])) {
                unset($config[$elmtName]);
            }
        }

        return $config;
    }

    /**
     * Get a firewall set with a predefined configuration or with additional setting
     *
     * @param string|null $configName Predefined configuration name
     * @param array       $options    Additional setting
     * @param Request     $request    Request
     *
     * @return FirewallInterface
     */
    public function getFirewall($configName = null, array $options = array(), Request $request = null)
    {
        if (!$request) {
            $request = $this->container->get('request');
        }

        $firewall = new $this->firewallClass();
        $firewall->setProvider($this);
        $firewall->setRequest($request);

        $this->setDefaults($firewall, $configName, $options);

        $this->firewalls[] = $firewall;

        return $firewall;
    }

    /**
     * Set the firewall class
     *
     * @param string $firewallClass Class for firewall objects
     *
     * @return $this
     */
    public function setFirewallClass($firewallClass)
    {
        $this->firewallClass = $firewallClass;

        return $this;
    }

    /**
     * Get lists of predefined ip
     *
     * @return array
     */
    public function getLists()
    {
        return $this->lists;
    }

    /**
     * get predefined configuration
     *
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * get predefined patterns
     *
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * Configure a firewall
     *
     * @param FirewallInterface $firewall   Firewall
     * @param string            $configName Predefined configuration name
     * @param array             $options    Additional setting
     *
     * @return $this
     */
    protected function setDefaults(FirewallInterface $firewall, $configName = null, array $options = array())
    {
        $config = $this->getNormalizedConfig($configName);

        $this->mergeOptions($config, $options);

        foreach ($config as $paramName => $paramValue) {
            $this->setDefault($firewall, $paramName, $paramValue, $config);
        }

        return $this;
    }

    /**
     * Set a default value for a configuration parameter
     *
     * @param Firewall $firewall Firewall
     * @param string   $param    Parameter name
     * @param string   $value    Parameter value
     * @param array    $config   Reference to the configuration
     *
     * @return $this
     */
    protected function setDefault(FirewallInterface $firewall, $param, $value, $config)
    {
        $configModel = $this->configModel[$param];

        switch ($param) {
            case 'lists':
                if (is_array($value)) {
                    $this->setDefaultLists($firewall, $value);
                }
                break;
            case 'entries':
                $this->setEntries($firewall, $config, $value);
                break;
            default:
                if (!is_null($value)) {
                    $method = $configModel['method'];
                    $firewall->$method($value);
                }
        }

        return $this;
    }

    /**
     * Set the default lists of the firewall
     *
     * @param FirewallInterface $firewall Firewall
     * @param array             $lists    Input lists
     *
     * @return $this
     */
    protected function setDefaultLists(FirewallInterface $firewall, array $lists)
    {
        foreach ($lists as $listName => $state) {
            if (isset($this->lists[$listName])) {
                $list = $this->lists[$listName];
                $firewall->addList($list, $listName, $state);
            } else {
                throw new \Exception(sprintf('Firewall list "%s" not found.', $listName));
            }
        }

        return $this;
    }

    /**
     * Set up firewall lists from independent input
     *
     * @param array $config Reference to the configuration
     *
     * @return $this
     */
    protected function setEntries(FirewallInterface $firewall, $config)
    {
        $entries = (isset($config['entries']) ? $config['entries'] : array());

        $blackEntries = array_keys($entries, false);
        $whiteEntries = array_keys($entries, true);

        if (count($blackEntries)) {
            $firewall->addList($blackEntries, 'blackedOptions', false);
        }

        if (count($whiteEntries)) {
            $firewall->addList($whiteEntries, 'whitedOptions', true);
        }

        return $this;
    }

    /**
     * Get the normalized configuration
     *
     * @param string|null $configName Configuration name
     *
     * @throws Exception If the configuration name is unknown
     *
     * @return array Normalized configuration
     */
    protected function getNormalizedConfig($configName = null)
    {
        if (isset($this->configs[$configName])) {
            $config = $this->configs[$configName];
        } elseif ($configName === null) {
            $arr = array();
            $config = $this->normalizeConfig($arr);
        } else {
            throw new \Exception(sprintf('Firewall configuration "%s" not found.', $configName));
        }

        return $config;
    }

    /**
     * Include on the fly generated options in the main configuration
     *
     * @param array &$config Reference to the configuration
     * @param array $options Options
     *
     * @return array Full configuration
     */
    protected function mergeOptions(&$config, $options)
    {
        foreach ($config as $paramName => $paramValue) {
            if (isset($options[$paramName])) {
                if (is_array($paramValue)) {
                    $config[$paramName] =  array_merge($config[$paramName], $options[$paramName]);
                } else {
                    $config[$paramName] = $options[$paramName];
                }
            }
        }

        return $config;
    }
}
