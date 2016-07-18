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
        if (!$container->getParameter('ongr_router.disable_alias')) {
            $container->setAlias('router', 'ongr_router.chain_router');
        }
        $container
            ->getDefinition('ongr_router.elasticsearch_route_provider')
            ->addMethodCall(
                'setManager',
                [new Reference($container->getParameter('ongr_router.manager'))]
            );
    }
}
