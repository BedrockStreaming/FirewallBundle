<?php
namespace M6Web\Bundle\FirewallBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('m6web_firewall');

        $rootNode
            ->children()
                // Add pre-defined configs management
                ->arrayNode('configs')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('list', 'lists')
                        ->fixXmlConfig('entry', 'entries')
                        ->children()
                            // Set firewall default state
                            ->booleanNode('default_state')->defaultFalse()->end()
                            ->scalarNode('error_code')->end()
                            ->scalarNode('error_message')->end()
                            ->booleanNode('throw_error')->defaultTrue()->end()
                            // Set pre-defined list status
                            ->arrayNode('lists')
                                ->useAttributeAsKey('name')
                                ->prototype('boolean')->defaultFalse()->end()
                            ->end()
                            // Set single entries status
                            ->arrayNode('entries')
                                ->useAttributeAsKey('template')
                                ->prototype('boolean')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                // Add pre-defined lists management
                ->arrayNode('lists')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->prototype('variable')
                            ->validate()
                                ->always(function ($list) {
                                    if (!is_array($list) && !is_string($list)) {
                                        throw new InvalidConfigurationException('Invalid configuration for path "m6web_firewall.lists": lists are any depth arrays of string values');
                                    }
                                    
                                    return $list;
                                })
                            ->end()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                // Add pattern support
                ->arrayNode('patterns')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('config')->end()
                            ->scalarNode('path')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
