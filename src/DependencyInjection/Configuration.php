<?php

namespace ComCompany\YousignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('yousign');

        $treeBuilder->getRootNode() // @phpstan-ignore-line
            ->children()
                ->arrayNode('eventHandlers')
                    ->children()
                        ->scalarNode('default')->end()
                    ->arrayNode('bindings')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('event')->end()
                                ->scalarNode('service')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
