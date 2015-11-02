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

use ONGR\RouterBundle\Routing\ChainRouter;
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ChainRouter.
 */
class ChainRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for testGenerate.
     *
     * @return array
     */
    public function generateProvider()
    {
        $cases = [];
        $message = 'None of the chained routers were able to generate route: ';
        $exception = 'Symfony\Component\Routing\Exception\RouteNotFoundException';

        // Case #0. Error message testing. No routers.
        $chainRouter = new ChainRouter();
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [],
            'absolute' => false,
            'expectedUrl' => null,
            'expectedException' => $exception,
            'expectedExceptionMessage' => $message . 'Route \'route\' not found',
        ];

        // Case #1. Error message testing. VersatileGeneratorInterface.
        $chainRouter = new ChainRouter();
        /** @var ChainedRouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Cmf\Component\Routing\ChainedRouterInterface');
        $router->expects($this->once())->method('generate')->willThrowException(new RouteNotFoundException());
        $router->expects($this->once())->method('supports')->with('route')->willReturn(true);
        $router->expects($this->once())->method('getRouteDebugMessage')->with('route', [])->willReturn('Test route');
        $chainRouter->add($router);
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [],
            'absolute' => false,
            'expectedUrl' => null,
            'expectedException' => 'Symfony\Component\Routing\Exception\RouteNotFoundException',
            'expectedExceptionMessage' => $message . 'Route \'Test route\' not found',
        ];

        // Case #2. Incapable router testing. Object route.
        $chainRouter = new ChainRouter();
        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->never())->method('generate');
        $chainRouter->add($router);
        $cases[] = [
            'router' => $chainRouter,
            'route' => new \stdClass(),
            'parameters' => [],
            'absolute' => false,
            'expectedUrl' => null,
            'expectedException' => 'Symfony\Component\Routing\Exception\RouteNotFoundException',
            'expectedExceptionMessage' => $message . 'Route \'stdClass\' not found',
        ];

        // Case #3. Incapable router testing. Unsupported route.
        $chainRouter = new ChainRouter();
        /** @var ChainedRouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Cmf\Component\Routing\ChainedRouterInterface');
        $router->expects($this->never())->method('generate');
        $router->expects($this->once())->method('supports')->with('route')->willReturn(false);
        $router->expects($this->once())->method('getRouteDebugMessage')->with('route', [])->willReturn('Test route');
        $chainRouter->add($router);
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [],
            'absolute' => false,
            'expectedUrl' => null,
            'expectedException' => 'Symfony\Component\Routing\Exception\RouteNotFoundException',
            'expectedExceptionMessage' => $message . 'Route \'route\' not found',
        ];

        // Case #4. Incapable router testing. Unsupported parameter.
        $chainRouter = new ChainRouter();
        /** @var ChainedRouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Cmf\Component\Routing\ChainedRouterInterface');
        $router->expects($this->never())->method('generate');
        $router->expects($this->once())->method('supports')->with('route')->willReturn(true);
        $router->expects($this->once())->method('getRouteDebugMessage')->with('route', [])->willReturn('Test route');
        $chainRouter->add($router);
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [new \stdClass()],
            'absolute' => false,
            'expectedUrl' => null,
            'expectedException' => 'Symfony\Component\Routing\Exception\RouteNotFoundException',
            'expectedExceptionMessage' => $message . 'Route \'route\' not found',
        ];

        // Case #5. Test logger.
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('debug');
        $chainRouter = new ChainRouter($logger);
        /** @var ChainedRouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Cmf\Component\Routing\ChainedRouterInterface');
        $router->expects($this->once())->method('generate')->willThrowException(new RouteNotFoundException());
        $router->expects($this->once())->method('supports')->with('route')->willReturn(true);
        $router->expects($this->once())->method('getRouteDebugMessage')->with('route', [])->willReturn('Test route');
        $chainRouter->add($router);
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [],
            'absolute' => false,
            'expectedUrl' => null,
            'expectedException' => 'Symfony\Component\Routing\Exception\RouteNotFoundException',
            'expectedExceptionMessage' => $message . 'Route \'Test route\' not found',
        ];

        // Case #6. Test valid route.
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $chainRouter = new ChainRouter();
        /** @var ChainedRouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Cmf\Component\Routing\ChainedRouterInterface');
        $router->expects($this->once())->method('generate')->willReturn('url');
        $router->expects($this->once())->method('supports')->with('route')->willReturn(true);
        $router->expects($this->once())->method('getRouteDebugMessage')->with('route', [])->willReturn('Test route');
        $chainRouter->add($router);
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [],
            'absolute' => false,
            'expectedUrl' => 'url',
        ];

        // Case #7. Test multiple routes.
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $chainRouter = new ChainRouter();
        /** @var ChainedRouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Cmf\Component\Routing\ChainedRouterInterface');
        $router->expects($this->once())->method('generate')->willReturn('url');
        $router->expects($this->once())->method('supports')->with('route')->willReturn(true);
        $router->expects($this->once())->method('getRouteDebugMessage')->with('route', [])->willReturn('Test route');
        $chainRouter->add($router, 0);
        /** @var ChainedRouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Cmf\Component\Routing\ChainedRouterInterface');
        $router->expects($this->once())->method('generate')->willThrowException(new RouteNotFoundException());
        $router->expects($this->once())->method('supports')->with('route')->willReturn(true);
        $router->expects($this->once())->method('getRouteDebugMessage')->with('route', [])->willReturn('Test route');
        $chainRouter->add($router, 1);
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [],
            'absolute' => false,
            'expectedUrl' => 'url',
        ];

        // Case #8. Null value as a parameter.
        $chainRouter = new ChainRouter();
        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->willReturn('expected-url');
        $chainRouter->add($router);
        $cases[] = [
            'router' => $chainRouter,
            'route' => 'route',
            'parameters' => [null],
            'absolute' => false,
            'expectedUrl' => 'expected-url',
        ];

        return $cases;
    }

    /**
     * Tests generate method.
     *
     * @param ChainRouter $router
     * @param string      $route
     * @param array       $parameters
     * @param bool        $absolute
     * @param string      $expectedUrl
     * @param string|null $expectedException
     * @param string      $expectedExceptionMessage
     *
     * @dataProvider generateProvider
     */
    public function testGenerate(
        ChainRouter $router,
        $route,
        array $parameters,
        $absolute,
        $expectedUrl,
        $expectedException = null,
        $expectedExceptionMessage = ''
    ) {
        if ($expectedException !== null) {
            $this->setExpectedException($expectedException, $expectedExceptionMessage);
        }

        $result = $router->generate($route, $parameters, $absolute);
        $this->assertEquals($expectedUrl, $result);
    }
}
