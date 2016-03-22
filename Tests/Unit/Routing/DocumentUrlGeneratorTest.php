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

use ONGR\RouterBundle\Routing\DocumentUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentUrlGeneratorTest extends WebTestCase
{
    /**
     * Tests getters and setters
     */
    public function testGettersAndSetters()
    {
        $routeMap = new \stdClass();
        $routeProvider = $this->getMock('Symfony\Cmf\Component\Routing\RouteProviderInterface');
        $collector = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $urlGenerator = new DocumentUrlGenerator($routeProvider);

        $urlGenerator->setRouteMap($routeMap);
        $retrievedRoute = $urlGenerator->getRouteMap();
        $urlGenerator->setCollector($collector);
        $retrievedCollector = $urlGenerator->getCollector();

        $this->assertEquals($retrievedRoute, $routeMap);
        $this->assertEquals($retrievedCollector, $collector);
    }

    /**
     * Tests debug message when given object is not an
     * instance of SeoAwareInterface
     */
    public function testGetDebugMessageWithBadObject()
    {
        $document = new \stdClass();
        $routeProvider = $this->getMock('Symfony\Cmf\Component\Routing\RouteProviderInterface');
        $urlGenerator = new DocumentUrlGenerator($routeProvider);
        $debugMessage = $urlGenerator->getRouteDebugMessage($document);

        $this->assertEquals(
            'Given route object must be an instance of SeoAwareInterface',
            $debugMessage
        );
    }

    /**
     * Tests exceptions in generate method
     *
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateException()
    {
        $document = new \stdClass();
        $routeProvider = $this->getMock('Symfony\Cmf\Component\Routing\RouteProviderInterface');
        $urlGenerator = new DocumentUrlGenerator($routeProvider);

        $urlGenerator->generate($document);
    }
}
