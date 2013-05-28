<?php
namespace M6Web\Bundle\FirewallBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class M6WebFirewallExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->flatLists($configs);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (!empty($config['configs']) ) {
            $this->configsLoad($config['configs'], $container);
        }

        if (!empty($config['lists']) ) {
            $this->listsLoad($config['lists'], $container);
        }
    }

    protected function configsLoad(array $config, ContainerBuilder $container)
    {
        $container->setParameter('m6web.firewall.configs', $config);
    }

    protected function listsLoad(array $config, ContainerBuilder $container)
    {
        $container->setParameter('m6web.firewall.lists', $config);
    }

    protected function flatLists(array &$configs)
    {
        foreach ($configs as &$config) {
            if (isset($config['lists'])) {
                foreach ($config['lists'] as &$list) {
                    $this->flatList($list);
                }
            }
        }

        return $configs;
    }

    protected function flatList(array &$list)
    {
        $return = array();
        array_walk_recursive($list, function($a) use (&$return) {
            $return[] = $a;
        });
        $list = $return;

        return $list;
    }
}
