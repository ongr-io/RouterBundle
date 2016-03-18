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
        $document->setUrl('/acme');

        $client = static::createClient();
        $router = $client->getContainer()->get('router');

        $url = $router->generate($document);

        $this->assertEquals($document->getUrl(), $url);
    }
    /**
     * This test checks if the document is fit for route generation
     */
    public function testDocumentRouteGeneration()
    {
        $document = new Product();
        $document->title = 'Acme';
        $document->setUrl('/acme');

        $client = static::createClient();
        $router = $client->getContainer()->get('router');
        $sub_routers =$router->all();
        $support = false;
        $message = '';
        foreach ($sub_routers as $router) {
            if (method_exists($router, 'supports') && $router->supports($document)) {
                $support = true;
                $message = $router->getRouteDebugMessage($document);
            }
        }

        $this->assertTrue($support);
        $this->assertStringMatchesFormat('The route object is fit for parsing to generate() method', $message);
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
