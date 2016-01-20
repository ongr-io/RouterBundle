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

/**
 * Resolves type names by configured document class.
 */
class SeoRoutesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $collector = $container->get('es.metadata_collector');
        $seoRoutes = $container->getParameter('ongr_router.seo_routes');
        $resolvedRoutes = [];

        foreach ($seoRoutes as $document => $controllerAction) {
            $document = $collector->getDocumentType($document);
            $resolvedRoutes[$document] = $controllerAction;
        }

        $definition = $container->getDefinition('ongr_router.elasticsearch_route_provider');
        $definition->replaceArgument(1, $resolvedRoutes);
    }
}
