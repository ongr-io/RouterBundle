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
                ->booleanNode('enable')
                    ->defaultTrue()
                    ->info('Replace default Symfony router with chain router')
                ->end()
                ->booleanNode('add_symfony_router')
                    ->defaultTrue()
                    ->info('Add default Symfony router to chain')
                ->end()
                ->booleanNode('add_ongr_router')
                    ->defaultTrue()
                    ->info('Add ongr router to chain')
                ->end()
                ->scalarNode('es_manager')
                    ->defaultValue('es.manager.default')
                    ->info('Elasticsearch manager to use in router')
                    ->example('es.manager.default')
                ->end()
                ->scalarNode('seo_key')
                    ->defaultValue(null)
                    ->info('Set specific url key for route search. Must be used with SeoAwareNestedTrait')
                ->end()
                ->arrayNode('seo_routes')
                    ->defaultValue([])
                    ->prototype('array')
                        ->children()
                            ->scalarNode('_route')
                                ->isRequired()
                                ->info('Route to be used by ONGR SEO URL generating and matching')
                                ->example('my_product_document_page')
                            ->end()
                            ->scalarNode('_controller')
                                ->isRequired()
                                ->info('Controller that will used by _route')
                                ->example('AcmeDemoBundle:User')
                            ->end()
                            ->scalarNode('_default_route')
                                ->isRequired()
                                ->example('my_product_show')
                                ->info('Route which will be used if generating route using ONGR SEO fails')
                            ->end()
                            ->scalarNode('_id_param')
                                ->isRequired()
                                ->example('productId')
                                ->info('Id field to be passed to _default_route')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
