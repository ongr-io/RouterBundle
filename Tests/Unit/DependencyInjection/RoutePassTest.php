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

use ONGR\RouterBundle\DependencyInjection\Compiler\SeoRoutesPass;
use ONGR\RouterBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Unit test for compiler pass files
 */
class RoutePassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Prepares and returns the container
     *
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        $container = new ContainerBuilder();
        $manager = $this->getMockBuilder('ONGR\ElasticsearchBundle\Service\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $collector = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $collector->expects($this->any())->method('getDocumentType')->will($this->returnValue('product'));
        $routes = [
            'AppBundle:Product' => 'AppBundle:Test:document'
        ];

        $container->set('es.metadata_collector', $collector);
        $container->set('es.manager.default', $manager);
        $provider = new Definition(
            'ONGR\RouterBundle\Routing\ElasticsearchRouteProvider',
            [
                $collector,
                ['routes to be changed']
            ]
        );
        $container->setDefinition(
            'ongr_router.elasticsearch_route_provider',
            $provider
        );
        $container->setParameter('ongr_router.seo_routes', $routes);
        $container->setParameter('ongr_router.disable_alias', false);
        $container->setParameter('ongr_router.manager', 'es.manager.default');

        return $container;
    }

    /**
     * Tests SeoRoutesPass
     */
    public function testSeoRoutesPass()
    {
        $container = $this->getContainer();
        (new SeoRoutesPass())->process($container);
        $expected = ['product'=>'AppBundle:Test:document'];

        $provider = $container->get('ongr_router.elasticsearch_route_provider');
        $this->assertEquals($provider->getRouteMap(), $expected);
    }

    /**
     * Tests SetRouterPass
     */
    public function testSetRouterPass()
    {
        $container = $this->getContainer();
        (new SetRouterPass())->process($container);

        $provider = $container->get('ongr_router.elasticsearch_route_provider');
        $this->assertNotNull($provider->getManager());
    }
}
