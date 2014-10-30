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

use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\RouterBundle\Service\SeoUrlMapper;

/**
 * Tests for url mapper class.
 */
class SeoUrlMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test cases provider for testGetLinkByKey().
     *
     * @return Array of test cases.
     */
    public function getTestGetLinkByKeyCases()
    {
        $out = [];

        // Case #0: document without urls.
        $document = $this->getEmptyDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'url1',
            'expectedUrl' => false,
        ];

        // Case #1: key exists.
        $document = $this->getDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'url1',
            'expectedUrl' => 'seo1/',
        ];

        // Case #2: key does not exist.
        $document = $this->getDocument();
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
     * @param object      $document        Document.
     * @param string      $requestedUrlKey Requested url.
     * @param string|bool $expectedUrl     Expected url or false.
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
     * @return Array of test cases.
     */
    public function getTestCheckDocumentUrlExistsCases()
    {
        $out = [];

        // Case #0: document without urls.
        $document = $this->getEmptyDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'url1',
            'expectedUrl' => false,
        ];

        // Case #1: url does not exist (neither identical nor similar),
        // so the first url key from the list should be returned.
        $document = $this->getDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'non-existing-url/',
            'expectedUrl' => reset($document->url)->key,
        ];

        // Case #2: identical url exists.
        $document = $this->getDocument();
        $out[] = [
            'document' => $document,
            'requestedUrl' => 'seo1/',
            'expectedUrl' => 'url1',
        ];

        // Case #3: case insensitive version of the url exists.
        $document = $this->getDocument();
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
     * @param object      $document     Document.
     * @param string      $requestedUrl Requested url.
     * @param string|bool $expectedUrl  Expected url or false.
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
     * @return \stdClass
     */
    private function getDocument()
    {
        $url1 = new \stdClass();
        $url1->url = 'seo1/';
        $url1->key = 'url1';

        $url2 = new \stdClass();
        $url2->url = 'seo2/';
        $url2->key = 'url2';

        $url3 = new \stdClass();
        $url3->url = 'seo3/';
        $url3->key = 'url3';

        $document = new \stdClass();
        $document->url = new \ArrayIterator([$url1, $url2, $url3]);

        return $document;
    }

    /**
     * Fake document without urls.
     *
     * @return DocumentInterface $document
     */
    private function getEmptyDocument()
    {
        $document = new \stdClass();

        return $document;
    }
}
