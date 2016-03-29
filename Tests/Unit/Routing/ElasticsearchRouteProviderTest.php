<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit\Routing;

use ONGR\RouterBundle\Routing\ElasticsearchRouteProvider;
use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;

class ElasticsearchRouteProviderTest extends AbstractElasticsearchTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getDataArray()
    {
        return [
            'default' => [
                'product' => [
                    [
                        '_id' => '1',
                        'title' => 'Acme',
                        'url' => '/acme',
                    ],
                    [
                        '_id' => '2',
                        'title' => 'Bar',
                        'url' => '/bar',
                    ],
                    [
                        '_id' => '3',
                        'title' => 'Foo',
                        'url' => '/foo',
                    ],
                ],
            ],
        ];
    }
    /**
     * Tests getters and setters
     */
    public function testGettersAndSetters()
    {
        $routeMap = ['1', '2', '3'];
        $collector = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $manager = $this->getMockBuilder('ONGR\ElasticsearchBundle\Service\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $routeProvider = new ElasticsearchRouteProvider($collector, $routeMap);

        $routeProvider->setManager($manager);

        $retrievedManager = $routeProvider->getManager();
        $retrievedMap = $routeProvider->getRouteMap();

        $this->assertEquals($retrievedMap, $routeMap);
        $this->assertEquals($retrievedManager, $manager);
    }

    /**
     * Tests the error when trying to get a route by name
     *
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByName()
    {
        $collector = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $routeProvider = new ElasticsearchRouteProvider($collector);
        $routeProvider->getRouteByName('not_existing_route');
    }

    /**
     * Tests if getRoutes returns an empty collection
     */
    public function testGetRoutesByName()
    {
        $collector = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $routeProvider = new ElasticsearchRouteProvider($collector);
        $names = ['any', 'array', 'of', 'names'];

        $this->assertEquals(
            new RouteCollection(),
            $routeProvider->getRoutesByNames($names)
        );
    }

    /**
     * Tests getRouteCollectionForRequest exception when routeMap is not loaded
     *
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testNoRouteMapDefined()
    {
        $collector = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $provider = new ElasticsearchRouteProvider($collector, []);
        $provider->setManager($this->getManager());
        $request = Request::create(
            '/acme',
            'GET'
        );
        $provider->getRouteCollectionForRequest($request);

    }

    /**
     * Tests getRouteCollectionForRequest exception manager is undefined
     *
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testNoManagerDefined()
    {
        $collector = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $provider = new ElasticsearchRouteProvider($collector, []);
        $request = Request::create(
            '/acme',
            'GET'
        );
        $provider->getRouteCollectionForRequest($request);

    }
}
