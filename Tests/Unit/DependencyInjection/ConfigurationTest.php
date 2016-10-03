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
use Symfony\Component\Yaml\Yaml;

/**
 * Unit test for configuration tree.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests configuration.
     */
    public function testConfiguration()
    {
        $processor = new Processor();
        $configs = [
            'ongr_router' => [
                'seo_routes' => [
                    'FooBundle:Person' => 'FooBundle:People:greet',
                    'BarBundle:Category' => 'FooBundle:Default:list'
                ]
            ]
        ];
        $processorConfig = $processor->processConfiguration(new Configuration(), $configs);
        $expectedConfiguration = [
            'manager' => 'es.manager.default',
            'seo_routes' => $configs['ongr_router']['seo_routes'],
            'disable_alias' => false,
            'router_priority' => -100,
        ];
        $this->assertEquals($expectedConfiguration, $processorConfig);
    }
}
