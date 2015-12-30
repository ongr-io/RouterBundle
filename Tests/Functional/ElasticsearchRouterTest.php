<?php

namespace ONGR\RouterBundle\Tests\Functional;

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;

class ElasticsearchRouterTest extends AbstractElasticsearchTestCase
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
     * Test the router matches path in document url field.
     */
    public function testElasticsearchDocumentUrlMatch()
    {
        $this->getManager();

        $client = static::createClient();
        $crawler = $client->request('GET', '/acme');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Acme', $crawler->html());
    }

    /**
     * Check router instance.
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testNotExistingRoute()
    {
        $this->getManager();

        $client = static::createClient();
        $client->request('GET', '/definitely-not-existing-route');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
