<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Functional;

use ONGR\ElasticsearchBundle\Test\ElasticsearchTestCase;

class SeoNestedUrlMatcherTest extends ElasticsearchTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getDataArray()
    {
        return [
            'default' => [
                'productwithnestedurl' => [
                    [
                        '_id' => 'product_with_nested_urls_id_1',
                        'id' => 'product_with_nested_urls_id_1',
                        'urls' => [
                            ['url' => 'product-with-nested-urls/', 'key' => 'foo_bar'],
                        ],
                        'expired_urls' => [],
                    ],
                    [
                        '_id' => 'product_with_nested_urls_id_2',
                        'id' => 'product_with_nested_urls_id_2',
                        'urls' => [
                            ['url' => 'product-with-nested-urls/', 'key' => 'foo_baz'],
                        ],
                        'expired_urls' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * Data provider for testNestedUrlMatch().
     *
     * @return array
     */
    public function getTestNestedUrlMatchCases()
    {
        $out = [];

        // Case #0: document with foo_bar seo_key must be loaded.
        $out[] = [
            'environment' => 'test_nested_url_1',
            'expectedResponse' => ['document_id' => 'product_with_nested_urls_id_1', 'seo_key' => 'foo_bar'],
        ];

        // Case #1: document with foo_baz seo_key must be loaded.
        $out[] = [
            'environment' => 'test_nested_url_2',
            'expectedResponse' => ['document_id' => 'product_with_nested_urls_id_2', 'seo_key' => 'foo_baz'],
        ];

        return $out;
    }

    /**
     * Document with matching url and key must be passed to an action.
     *
     * @param string $environment      Launch request using this environment container.
     * @param array  $expectedResponse Expected response from controller showing correct document id.
     *
     * @dataProvider getTestNestedUrlMatchCases()
     */
    public function testNestedUrlMatch($environment, $expectedResponse)
    {
        $client = self::createClient(['environment' => $environment]);
        $client->request('GET', '/product-with-nested-urls/');

        $response = $client->getResponse();

        $this->assertTrue($response->isOk(), 'Response should be OK. Got ' . $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResponse), $content);
    }
}
