<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit\DependencyInjection\Compiler;

use ONGR\RouterBundle\DependencyInjection\Compiler\SeoAnalyzerAwarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SeoAnalyzerAwarePassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests if process() method injects analyzers into connection.
     */
    public function testProcess()
    {
        $connectionDefMock = $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->setMethods(['addMethodCall'])
            ->getMock();

        $testAnalyzers = ['testAnalyzer' => []];

        $connectionDefMock
            ->expects($this->once())
            ->method('addMethodCall')
            ->with(
                'updateSettings',
                [
                    [
                        'body' => [
                            'settings' => [
                                'analysis' => [
                                    'analyzer' => $testAnalyzers,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $definition = new Definition(
            'Elasticsearch\ORM\Manager',
            [
                $connectionDefMock,
                new Definition('test'),
                [],
                [],
            ]
        );

        $container = new ContainerBuilder();
        $container->setParameter(
            'es.managers',
            [
                'default' => [],
            ]
        );
        $container->setParameter('ongr_router.analyzers', $testAnalyzers);
        $container->setDefinitions(
            [
                'es.manager.default' => $definition,
            ]
        );

        $compiler = new SeoAnalyzerAwarePass();
        $compiler->process($container);
    }
}
