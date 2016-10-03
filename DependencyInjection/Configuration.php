<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see
 *      {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ongr_router');

        $rootNode
            ->children()
                ->booleanNode('disable_alias')
                    ->info('disables aliasing of Symfony router in case it is done somewhere else')
                    ->defaultFalse()
                ->end()
                ->integerNode('router_priority')
                    ->defaultValue(-100)
                    ->info('The priority with which the ONGR router is set to the chain router')
                ->end()
                ->scalarNode('manager')
                    ->defaultValue('es.manager.default')
                    ->info('Elasticsearch manager to use in the ONGR default router')
                    ->example('es.manager.default')
                ->end()
                ->arrayNode('seo_routes')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
