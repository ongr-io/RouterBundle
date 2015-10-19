<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit\DependencyInjection;

use ONGR\RouterBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testConfiguration.
     *
     * @return array
     */
    public function getTestConfigurationData()
    {
        $out = [];

        // Case #0 test default values.
        $out[] = [
            [],
            'es.manager.default',
            [],
        ];

        // Case #1 test custom values.
        $out[] = [
            [
                'es_manager' => 'custom',
                'seo_routes' => [
                    'product' => [
                        '_route' => 'val1',
                        '_controller' => 'val2',
                        '_default_route' => 'val3',
                        '_id_param' => 'val4',
                    ],
                ],
            ],
            'custom',
            [
                'product' => [
                    '_route' => 'val1',
                    '_controller' => 'val2',
                    '_default_route' => 'val3',
                    '_id_param' => 'val4',
                ],
            ],
        ];

        return $out;
    }

    /**
     * Tests if expected configuration structure works well.
     *
     * @param array  $config          Configuration array.
     * @param string $expectedManager Expected manager.
     * @param array  $expectedRoutes  Expected routes.
     *
     * @dataProvider getTestConfigurationData()
     */
    public function testConfiguration($config, $expectedManager, $expectedRoutes)
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new Configuration(), [$config]);

        $this->assertEquals($expectedManager, $processedConfig['es_manager'], 'Incorrect manager passed');
        $this->assertEquals($expectedRoutes, $processedConfig['seo_routes'], 'Incorrect routes passed');
    }
}
