<?php

namespace ONGR\RouterBundle\Tests\Functional;

use ONGR\RouterBundle\Tests\app\fixture\AppBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRouteGenerationTest extends WebTestCase
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

//        $url = $router->generate('ongr_route_product', ['document' => $document]);
        $url = $router->generate($document);

        $this->assertEquals($document->url, $url);
    }
}
