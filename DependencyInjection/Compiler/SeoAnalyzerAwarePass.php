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
use Symfony\Component\DependencyInjection\Definition;

/**
 * Adds SEO analyzers to all ES managers.
 */
class SeoAnalyzerAwarePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach (array_keys($container->getParameter('es.managers')) as $managerName) {
            $manager = $container->getDefinition(sprintf('es.manager.%s', $managerName));

            /** @var Definition $connection */
            $connection = $manager->getArgument(0);
            $connection->addMethodCall(
                'updateSettings',
                [
                    [
                        'body' => [
                            'settings' => [
                                'analysis' => [
                                    'analyzer' => $container->getParameter('ongr_router.analyzers'),
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }
    }
}
