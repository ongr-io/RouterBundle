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

use ONGR\RouterBundle\Routing\RequestContext;
use ONGR\RouterBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;

/**
 * Tests for url matcher class.
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns router instance.
     *
     * @param ContainerBuilder $container Container.
     *
     * @return Router
     */
    protected function getRouter($container = null)
    {
        $container = $container ? $container : new ContainerBuilder();

        $customModel = new \stdClass();
        $container->set('es.manager', $customModel);
        $container->setParameter('ongr_router.seo_route', []);
        $container->setParameter('ongr_router.url_key', null);
        $container->setParameter('ongr_router.manager', 'default');

        $seoMapper = $this->getMock('ONGR\RouterBundle\Service\SeoUrlMapper');
        $container->set('ongr_router.seo_url_mapper', $seoMapper);

        $request = $this->getMock('\stdClass', ['getPathInfo']);
        $container->set('request', $request);
        $container->addScope(new Scope('request'));
        $container->enterScope('request');

        $context = new RequestContext('http://www.europeanmedia.com/', 'GET', 'www.europeanmedia.com');

        return new Router(
            $container,
            false,
            [
                'matcher_class' => 'stdClass',
                'generator_class' => 'stdClass',
            ],
            $context
        );
    }

    /**
     * Test if we get needed matcher.
     */
    public function testGetMatcher()
    {
        $router = $this->getRouter();

        $this->assertInstanceOf('ONGR\RouterBundle\Routing\SeoUrlMatcher', $router->getMatcher());
    }

    /**
     * Generator test.
     */
    public function testGetGenerator()
    {
        $router = $this->getRouter();

        $context = new RequestContext();
        $router->setContext($context);

        $this->assertInstanceOf('ONGR\RouterBundle\Routing\SeoUrlGenerator', $router->getGenerator());

        // Test if local cache works.
        $this->assertInstanceOf('ONGR\RouterBundle\Routing\SeoUrlGenerator', $router->getGenerator());

        // Assert if has context.
        $this->assertNotNull($router->getGenerator()->getContext());
        $this->assertEquals(spl_object_hash($context), spl_object_hash($router->getGenerator()->getContext()));
    }

    /**
     * Check if we get correct ssl trust headers.
     */
    public function testGetSSlOriginTrustHeaders()
    {
        $this->assertEquals(
            ['Access-Control-Allow-Origin' => 'https://www.europeanmedia.com'],
            $this->getRouter()->getSSlOriginTrustHeaders()
        );
    }

    /**
     * We should not try to get path info from request if request scope is not entered.
     */
    public function testGetGeneratorNoRequestScope()
    {
        $container = new ContainerBuilder();
        $router = $this->getRouter($container);

        $container->get('request')->expects($this->never())->method('getPathInfo');
        $container->leaveScope('request');

        $this->assertInstanceOf('ONGR\RouterBundle\Routing\SeoUrlGenerator', $router->getGenerator());
    }
}
