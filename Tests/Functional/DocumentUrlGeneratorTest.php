<?php

namespace ONGR\RouterBundle\Tests\Functional;

use ONGR\RouterBundle\Tests\app\fixture\AppBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentUrlGeneratorTest extends WebTestCase
{
    /**
     * Check router generation.
     */
    public function testRouteGeneration()
    {
        $document = new Product();
        $document->title = 'Acme';
        $document->url = '/acme';

        $client = static::createClient();
        $router = $client->getContainer()->get('router');

        $url = $router->generate('ongr_route_product', ['document' => $document]);

        $this->assertEquals($document->url, $url);
    }

    /**
     * This checks Symfony route generation with additional parameters.
     */
    public function testStaticRouteGenerationWithAdditionalParameters()
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');

        $url = $router->generate('ongr_router.home', ['param' => ['foo', 'bar']]);

        $this->assertEquals('/?param%5B0%5D=foo&param%5B1%5D=bar', $url);
    }
}
