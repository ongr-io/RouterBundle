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

use ONGR\ElasticsearchBundle\Service\Manager;
use ONGR\RouterBundle\Document\UrlNested;
use ONGR\RouterBundle\Routing\SeoUrlMatcher;
use ONGR\RouterBundle\Service\SeoUrlMapper;
use ONGR\RouterBundle\Tests\app\fixture\Acme\TestBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher;
use Symfony\Component\Routing\RequestContext;

class SeoUrlMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestMatchData()
    {
        $url1 = new UrlNested();
        $url1->url = 'foo';
        $url1->key = 'foo_key';

        $product = new Product();
        $product->urls = new \ArrayIterator([$url1]);

        $out = [];

        // Case #0: Empty map = not found
        $out[] = ['foo', 'foo', null, [$product, []], []];

        // Case #1: Documents does not exist = not found
        $out[] = ['foo', 'foo', null, []];

        // Case #2: Product found
        $out[] = [
            'foo',
            'foo',
            array_merge($this->getDefaultResult(), ['seoKey' => 'foo_key']),
            [$product]
        ];

        // Case #3: Product without seoKey found
        $out[] = [
            'bar',
            'bar',
            array_merge($this->getDefaultResult(), ['seoKey' => null]),
            []
        ];

        // Case #4: Multiple products found
        $out[] = [
            'foo',
            'foo',
            array_merge($this->getDefaultResult(), ['seoKey' => 'foo_key']),
            [$product, []]
        ];

        // Case #5: slash trimming
        $customUrl = new UrlNested();
        $customUrl->url = 'longer/foo/url';
        $customProduct = clone $product;
        $customProduct->urls = new \ArrayIterator([$customUrl]);

        $out[] = [
            '/longer/foo/url',
            'longer/foo/url',
            array_merge($this->getDefaultResult(), ['seoKey' => null]),
            [$customProduct]
        ];

        // Case #6: expired url = not found
        $document5 = new Product();
        $document5->urls = new \ArrayIterator();

        $out[] = ['seo2', 'seo2', null, [$document5]];

        return $out;
    }

    /**
     * Tests for matcher. Do not tests ES query.
     *
     * @param string     $url               Url given to match.
     * @param string     $expectedQueryUrl  Expected url that will be used at matching query.
     * @param array|null $expectedResult    Expected response of 'match'.
     * @param array      $documentsData     Array of found documents.
     * @param array      $overwriteMap      Types map to use. Will use default one, if null provided.
     *
     * @dataProvider getTestMatchData
     */
    public function testMatch($url, $expectedQueryUrl, $expectedResult, $documentsData, $overwriteMap = null)
    {
        $map = ($overwriteMap !== null) ? $overwriteMap : $this->getDefaultMap();

        /** @var SeoUrlMatcher|\PHPUnit_Framework_MockObject_MockObject $matcher */
        $matcher = $this->getMockBuilder('ONGR\RouterBundle\Routing\SeoUrlMatcher')
            ->setConstructorArgs([$this->getParentMatcherMock(), $this->getManagerMock($documentsData), $map])
            ->setMethods(['getSearch'])
            ->getMock();
        $matcher->setSeoUrlMapper(new SeoUrlMapper());
        $matcher->expects($this->once())
            ->method('getSearch')
            ->with($expectedQueryUrl);

        if ($expectedResult === null) {
            $this->setExpectedException('Symfony\Component\Routing\Exception\ResourceNotFoundException');
        }

        $result = $matcher->match($url);

        $this->assertTrue(is_array($result), 'Result must be an array.');
        unset($result['document']);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testing redirect.
     *
     * @return array
     */
    public function getTestMatchRedirectCaseData()
    {
        $out = [];

        // Case #0: Url is expired.
        $url1 = new UrlNested();
        $url1->url = 'seo1';
        $url1->key = 'url1';
        $document1 = new Product();
        $document1->urls = new \ArrayIterator([$url1]);

        $out[] = ['seo2', 'seo2', '/seo1', [$document1]];

        // Case #1: Not all urls are expired.
        $url2 = new UrlNested();
        $url2->url = 'seo1';
        $url2->key = 'url1';
        $document2 = new Product();
        $document2->urls = new \ArrayIterator([$url2]);
        $out[] = ['seo2', 'seo2', '/seo1', [$document2]];

        // Case #2: Redirect to original url, which is not necessarily the first one.
        $url51 = new UrlNested();
        $url51->url = 'Seo1';
        $url51->key = 'url1';
        $url52 = new UrlNested();
        $url52->url ='Seo2';
        $url52->key = 'url2';

        $document5 = new Product();
        $document5->urls = new \ArrayIterator([$url51, $url52]);
        $out[] = ['seo2', 'seo2', '/Seo2', [$document5]];

        return $out;
    }

    /**
     * Tests match method redirect case.
     *
     * @param string    $url                 Url given to match.
     * @param string    $expectedQueryUrl    Expected url that will be used at matching query.
     * @param array     $expectedRedirectTo  Expected path of redirect.
     * @param array     $documentsData       Array of found documents.
     *
     * @dataProvider getTestMatchRedirectCaseData
     */
    public function testMatchRedirectCase($url, $expectedQueryUrl, $expectedRedirectTo, $documentsData)
    {
        $parentMatcher = $this->getParentMatcherMock(false);
        $parentMatcher->expects($this->once())
            ->method('redirect')
            ->with($expectedRedirectTo);

        /** @var SeoUrlMatcher|\PHPUnit_Framework_MockObject_MockObject $matcher */
        $matcher = $this->getMockBuilder('ONGR\RouterBundle\Routing\SeoUrlMatcher')
            ->setConstructorArgs([$parentMatcher, $this->getManagerMock($documentsData), $this->getDefaultMap()])
            ->setMethods(['getSearch'])
            ->getMock();
        $matcher->setSeoUrlMapper(new SeoUrlMapper());

        $matcher->expects($this->any())
            ->method('getSearch')
            ->with($expectedQueryUrl);

        $matcher->match($url);
    }

    /**
     * Should throw exception when https is not allowed.
     *
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @expectedExceptionMessage Non-http urls are not processed
     */
    public function testMatchHttpsNotAllowed()
    {
        $context = $this->getMock('\stdClass', ['getScheme']);
        $context
            ->expects($this->once())
            ->method('getScheme')
            ->will($this->returnValue('https'));

        $parentMatcher = $this->getParentMatcherMock(true, $context);

        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this
            ->getMockBuilder('Elasticsearch\ORM\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $matcher = new SeoUrlMatcher($parentMatcher, $manager, [], false);
        $matcher->setSeoUrlMapper(new SeoUrlMapper());
        $matcher->match('seo1');
    }

    /**
     * Tests if exception is thrown without using url mapper.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Seo url mapper is not set.
     */
    public function testGetLinkWithoutUrlMapper()
    {
        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this
            ->getMockBuilder('Elasticsearch\ORM\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $matcher = new SeoUrlMatcher($this->getParentMatcherMock(), $manager, [], false);
        $matcherReflection = new \ReflectionClass('ONGR\RouterBundle\Routing\SeoUrlMatcher');
        $method = $matcherReflection->getMethod('getLink');
        $method->setAccessible(true);
        $method->invokeArgs($matcher, [new \stdClass(), '']);
    }

    /**
     * @param bool                  $neverRedirects     True if matcher does not expect to get any 'redirect' call.
     * @param null|RequestContext   $context            Context to use. Will create new one, if null.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|RedirectableUrlMatcher
     */
    private function getParentMatcherMock($neverRedirects = true, $context = null)
    {
        /** @var RedirectableUrlMatcher|\PHPUnit_Framework_MockObject_MockObject $parentMatcher */
        $parentMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RedirectableUrlMatcher')
            ->disableOriginalConstructor()
            ->setMethods(['redirect', 'getContext',])
            ->getMock();

        if ($context === null) {
            $context = new RequestContext();
        }

        $parentMatcher
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));

        if ($neverRedirects) {
            $parentMatcher->expects($this->never())
                ->method('redirect');
        }

        return $parentMatcher;
    }

    /**
     * @param array $executeResponse    Response of execute() at Repository.
     *
     * @return Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getManagerMock($executeResponse)
    {
        // Elasticsearch setup.
        $repository = $this
            ->getMockBuilder('ElasticsearchBundle\Service\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this
            ->getMockBuilder('ElasticsearchBundle\Service\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getMetadataCollector'])
            ->getMock();


        /** @var array|\PHPUnit_Framework_MockObject_MockObject $mapping */
        $mapping = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\MetadataCollector')
            ->disableOriginalConstructor()
            ->setMethods(['getDocumentMapping'])
            ->getMock();

        $documentMap = ['type' => 'product'];

        $mapping->expects($this->any())
            ->method('getDocumentMapping')
            ->willReturn($documentMap);

        $manager->expects($this->any())
            ->method('getMetadataCollector')
            ->willReturn($mapping);

        $repository
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($executeResponse));

        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $manager;
    }

    /**
     * Default map used at tests.
     *
     * @return array
     */
    private function getDefaultMap()
    {
        return [
            'product' => $this->getDefaultResult(),
        ];
    }

    /**
     * Default expected result for matcher tests.
     *
     * @return array
     */
    private function getDefaultResult()
    {
        return [
            '_controller' => 'testModelTestHandler',
            '_route' => 'testModelTestRoute',
        ];
    }
}
