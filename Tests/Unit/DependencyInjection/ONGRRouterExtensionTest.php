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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use ONGR\RouterBundle\DependencyInjection\ONGRRouterExtension;

/**
 * Unit tests for Extension class.
 */
class ONGRRouterExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestLoadMethodData()
    {
        $out = [];

        $container = new ContainerBuilder();

        $config = [
            'manager' => 'stash.memcache',
            'seo_routes' => [
                'AppBundle:Product' => 'AppBundle:Test:document'
            ],
        ];
        // Case #0 we need manager parameter
        $out[] = [$config, $container, 'ongr_router.manager'];
        // Case #1 we need seo_routes parameter
        $out[] = [$config, $container, 'ongr_router.seo_routes'];

        return $out;
    }

    /**
     * Test if we are able to load parameters.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param string           $expectedId
     *
     * @dataProvider getTestLoadMethodData
     */
    public function testLoadMethod($config, $container, $expectedId)
    {
        $extension = new ONGRRouterExtension();
        $extension->load(['ongr_router' => $config], $container);

        $this->assertTrue($container->hasParameter($expectedId));
    }

    /**
     * Test if the exception is thrown with bad configuration
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoadMethodException()
    {
        $container = new ContainerBuilder();
        $config = ['not_a_parameter' => 'not_a_value'];
        $extension = new ONGRRouterExtension();
        $extension->load(['ongr_router' => $config], $container);
    }
}
