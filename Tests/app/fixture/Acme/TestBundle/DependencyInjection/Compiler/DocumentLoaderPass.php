<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\app\fixture\Acme\TestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * SeoAwareTrait and SeoAwareNestedTrait can not be in the ES index together.
 *
 * This compiler pass removes one of document definitions regarding the test environment.
 */
class DocumentLoaderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $manager = $container->getDefinition('es.manager.default');

        /** @var Definition $classMetadataCollection */
        $classMetadataCollection = $manager->getArgument(1);

        $bundlesMapping = $classMetadataCollection->getArgument(0);

        switch ($container->getParameter('kernel.environment')) {
            case 'test':
                unset($bundlesMapping['AcmeTestBundle:ProductWithNestedUrl']);
                break;
            default:
                unset($bundlesMapping['AcmeTestBundle:Product']);
                break;
        }

        $classMetadataCollection->setArguments([$bundlesMapping]);

        $manager->setArguments(
            [
                $manager->getArgument(0),
                $classMetadataCollection,
            ]
        );

        $container->setDefinition('es.manager.default', $manager);
    }
}
