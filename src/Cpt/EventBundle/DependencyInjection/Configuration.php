<?php

namespace Cpt\EventBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
        $rootNode = $treeBuilder->root('event');
        
        $rootNode
            ->children()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('comment')->defaultValue('Cpt\\BlogBundle\\Entity\\Comment')->end()
                        ->scalarNode('user')->defaultValue('Application\\Sonata\\UserBundle\\Entity\\User')->end()
                        ->scalarNode('event')->defaultValue('Cpt\\EventBundle\\Entity\\Event')->end()
                        ->scalarNode('registration')->defaultValue('Cpt\\EventBundle\\Entity\\Registration')->end()
                    ->end()
                ->end()
             ->end();
        
        return $treeBuilder;
    }
    
}
