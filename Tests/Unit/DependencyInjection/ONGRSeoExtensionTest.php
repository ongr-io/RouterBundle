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

use ONGR\RouterBundle\DependencyInjection\ONGRRouterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ONGRSeoExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestBuildData()
    {
        $out = [];

        // Case #0 check if request context class is set.
        $out[] = ['router.request_context.class', 'ONGR\RouterBundle\Routing\RequestContext'];

        // Case #1 check if es manager is set.
        $out[] = ['ongr_router.manager', 'es.manager.default'];

        // Case #2 check if seo routes is set.
        $out[] = ['ongr_router.seo_route', []];

        // Case #3. Check if use_chain_router is set.
        $out[] = ['ongr_router.enable', true];

        // Case #4. Check if add_default_router is set.
        $out[] = ['ongr_router.add_symfony_router', true];

        // Case #5. Check if use_chain_router is set.
        $out[] = ['ongr_router.add_ongr_router', true];

        return $out;
    }

    /**
     * Tests extension build method.
     *
     * @param string $parameter Parameter.
     * @param string $expected  Expected parameter.
     *
     * @dataProvider getTestBuildData()
     */
    public function testBuild($parameter, $expected)
    {
        $extension = new ONGRRouterExtension();
        $container = new ContainerBuilder();
        $container->set('es.manager.default', new \stdClass());

        $extension->load([], $container);

        $this->assertTrue($container->hasParameter($parameter), 'Parameter was not found.');
        $this->assertEquals($expected, $container->getParameter($parameter), 'Parameter did not met expected value.');
    }
}
