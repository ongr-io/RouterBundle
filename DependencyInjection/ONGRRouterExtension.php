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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ONGRRouterExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('config.yml');
        $loader->load('services.yml');
        $loader->load('router.yml');

        $container->setParameter('ongr_router.manager', $config['es_manager']);
        $container->setParameter('ongr_router.seo_key', $config['seo_key']);
        $container->setParameter('ongr_router.seo_route', $config['seo_routes']);
        $container->setParameter('ongr_router.enable', $config['enable']);
        $container->setParameter('ongr_router.add_symfony_router', $config['add_symfony_router']);
        $container->setParameter('ongr_router.add_ongr_router', $config['add_ongr_router']);
    }
}
