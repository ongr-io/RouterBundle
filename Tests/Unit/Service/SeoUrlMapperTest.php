<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit\Service;

use ONGR\RouterBundle\Document\SeoAwareTrait;
use ONGR\RouterBundle\Document\UrlObject;
use ONGR\RouterBundle\Service\SeoUrlMapper;
use ONGR\RouterBundle\Tests\app\fixture\Acme\TestBundle\Document\Product;

/**
 * Tests for url mapper class.
 */
class SeoUrlMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test cases provider for testGetLinkByKey().
     *
     * @return Array Of test cases.
     */
    public function getTestGetLinkByKeyCases()
    {
        $out = [];

        // Case #0: document without urls.
        $document = $this->getEmptyProductDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'url1',
            'expectedUrl' => false,
        ];

        // Case #1: key exists.
        $document = $this->getProductDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'url1',
            'expectedUrl' => 'seo1/',
        ];

        // Case #2: key does not exist.
        $document = $this->getProductDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'non-existing-url-key',
            'expectedUrl' => false,
        ];

        return $out;
    }

    /**
     * Method test getLinkByKey().
     *
     * @param SeoAwareTrait $document        Document.
     * @param string        $requestedUrlKey Requested url.
     * @param string|bool   $expectedUrl     Expected url or false.
     *
     * @dataProvider getTestGetLinkByKeyCases
     */
    public function testGetLinkByKey($document, $requestedUrlKey, $expectedUrl)
    {
        $mapper = new SeoUrlMapper();

        $this->assertEquals($mapper->getLinkByKey($document, $requestedUrlKey), $expectedUrl);
    }

    /**
     * Test cases provider for testCheckDocumentUrlExists().
     *
     * @return Array Of test cases.
     */
    public function getTestCheckDocumentUrlExistsCases()
    {
        $out = [];

        // Case #0: document without urls.
        $document = $this->getEmptyProductDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'url1',
            'expectedUrl' => false,
        ];

        // Case #1: url does not exist (neither identical nor similar),
        // so the first url key from the list should be returned.
        $document = $this->getProductDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'non-existing-url/',
            'expectedUrl' => $document->getUrls()->current()->getKey(),
        ];

        // Case #2: identical url exists.
        $document = $this->getProductDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'seo1/',
            'expectedUrl' => 'url1',
        ];

        // Case #3: case insensitive version of the url exists.
        $document = $this->getProductDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'Seo1/',
            'expectedUrl' => 'url1',
        ];

        return $out;
    }

    /**
     * Method to test checkDocumentUrlExists().
     *
     * @param SeoAwareTrait $document     Document.
     * @param string        $requestedUrl Requested url.
     * @param string|bool   $expectedUrl  Expected url or false.
     *
     * @dataProvider getTestCheckDocumentUrlExistsCases
     */
    public function testCheckDocumentUrlExists($document, $requestedUrl, $expectedUrl)
    {
        $mapper = new SeoUrlMapper();

        $this->assertEquals($mapper->checkDocumentUrlExists($document, $requestedUrl), $expectedUrl);
    }

    /**
     * Returns fake document object with urls.
     *
     * @return Product
     */
    private function getProductDocument()
    {
        $url1 = new UrlObject();
        $url1->setUrl('seo1/');
        $url1->setKey('url1');

        $url2 = new UrlObject();
        $url2->setUrl('seo2/');
        $url2->setKey('url2');

        $url3 = new UrlObject();
        $url3->setUrl('seo3/');
        $url3->setKey('url3');

        $document = new Product();
        $document->setUrls(new \ArrayIterator([$url1, $url2, $url3]));

        return $document;
    }

    /**
     * Fake document without urls.
     *
     * @return Product
     */
    private function getEmptyProductDocument()
    {
        $document = new Product();

        return $document;
    }
}
