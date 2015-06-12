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

/**
 * Changes the Router implementation.
 */
class SetRouterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Overwrite default router by alias only.
        if ($container->hasParameter('ongr_router.use_chain_router')
            && $container->getParameter('ongr_router.use_chain_router') === true
        ) {
            $container->setAlias('router', 'ongr_router.chain_router');
        }

        $chainRouter = $container->getDefinition('ongr_router.chain_router');

        if ($container->hasParameter('ongr_router.add_default_router')
            && $container->getParameter('ongr_router.add_default_router') === true
        ) {
            $chainRouter->addMethodCall('add', [new Reference('router.default'), 100]);
        }

        if ($container->hasParameter('ongr_router.add_ongr_router')
            && $container->getParameter('ongr_router.add_ongr_router') === true
        ) {
            $chainRouter->addMethodCall('add', [new Reference('ongr_router.router'), -100]);
        }
    }
}
