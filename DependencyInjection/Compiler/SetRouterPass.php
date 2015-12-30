<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SetRouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // only replace the default router by overwriting the 'router' alias if config tells us to
        if ($container->hasParameter('ongr_router.enable') && $container->getParameter('ongr_router.enable')) {
            $container->setAlias('router', 'ongr_router.chain_router');
        }

        $container
            ->getDefinition('ongr_router.elasticsearch_route_provider')
            ->addMethodCall(
                'setManager',
                [new Reference($container->getParameter('ongr_router.manager'))]
            );

        $chainRouter = $container->getDefinition('ongr_router.chain_router');
        $routers = $container->getParameter('ongr_router.routers');

        foreach ($routers as $router => $priority) {
            $chainRouter->addMethodCall('add', [new Reference($router), $priority]);
        }
    }
}