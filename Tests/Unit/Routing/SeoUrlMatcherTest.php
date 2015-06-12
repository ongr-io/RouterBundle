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

use ONGR\ElasticsearchBundle\Mapping\ClassMetadata;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\RouterBundle\Document\UrlObject;
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
        $object = new Product();
        $testDocument = 'product';

        $url1 = new UrlObject();
        $url1->setUrl('test/');
        $url1->setKey('test_key');

        $object->setExpiredUrls([]);
        $object->setUrls(new \ArrayIterator([$url1]));

        $expiredUrlObject = new Product();
        $expiredUrlObject->setExpiredUrls([md5('test/')]);

        $url2 = new UrlObject();
        $url2->setUrl('test2/');
        $expiredUrlObject->setUrls(new \ArrayIterator([$url2]));

        return [
            // Case #0.
            [[], [], 'test/', false, true],
            // Case #1.
            [
                // Map.
                [
                    $testDocument => [
                        '_controller' => 'testModelTestHandler',
                        '_route' => 'testModelTestRoute',
                    ],
                ],
                // Documents data.
                [$object],
                // Url.
                'test/',
                // Seo match.
                true,
                // Seek.
                true,
                // Return.
                [
                    '_controller' => 'testModelTestHandler',
                    '_route' => 'testModelTestRoute',
                    'seoKey' => 'test_key',
                ],
            ],
            // Case #2.
            [
                // Map.
                [
                    $testDocument => [
                        '_controller' => 'testModelTestHandler',
                        '_route' => 'testModelTestRoute',
                    ],
                    'TestModel2' => [
                        '_controller' => 'testModelTestHandler2',
                        '_route' => 'testModelTestRoute2',
                    ],
                ],
                // Documents data.
                [$expiredUrlObject, $object],
                // Url.
                'test/',
                // Seo match.
                true,
                // Seek.
                true,
                // Return.
                [
                    '_controller' => 'testModelTestHandler',
                    '_route' => 'testModelTestRoute',
                    'seoKey' => 'test_key',
                ],
            ],
            // Case #3.
            [
                // Map.
                [
                    $testDocument => [
                        '_controller' => 'testModelTestHandler',
                        '_route' => 'testModelTestRoute',
                    ],
                ],
                // Documents data.
                [$object],
                // Url.
                '',
                // Seo match.
                true,
                // Seek.
                false,
                // Return.
                [
                    '_controller' => 'testModelTestHandler',
                    '_route' => 'testModelTestRoute',
                ],
            ],
        ];
    }

    /**
     * Test matcher.
     *
     * @param array  $map           Model to controller mapping.
     * @param array  $documentsData Array of found documents.
     * @param string $url           Matched url.
     * @param bool   $seoMatch      True if should find document by url.
     * @param bool   $seek          True if should try to find document by url.
     * @param array  $return        Return.
     *
     * @dataProvider getTestMatchData
     */
    public function testMatch($map, $documentsData, $url, $seoMatch, $seek, $return = [])
    {
        // Parent matcher setup.
        /** @var RedirectableUrlMatcher|\PHPUnit_Framework_MockObject_MockObject $parentMatcher */
        $parentMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RedirectableUrlMatcher')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'redirect',
                    'getContext',
                ]
            )
            ->getMock();
        $parentMatcher
            ->expects($this->any())
            ->method('redirect');
        $parentMatcher
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue(new RequestContext()));

        // Elasticsearch setup.
        $repository = $this
            ->getMockBuilder('ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this
            ->getMockBuilder('ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getDocumentMapping'])
            ->getMock();

        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $mapping */
        $mapping = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->setMethods(['getType'])
            ->getMock();

        reset($map);
        $mapping->expects($this->any())
            ->method('getType')
            ->willReturn(key($map));

        $manager
            ->expects($this->any())
            ->method('getDocumentMapping')
            ->willReturn($mapping);

        $repository
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($documentsData));

        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        // Matcher testing.
        $matcher = new SeoUrlMatcher($parentMatcher, $manager, $map);

        // Mocking SeoUrlMapper.
        $seoUrlMapper = $this->getSeoUrlMapperMock($url, isset($return['seoKey']) ? $return['seoKey'] : null);
        $matcher->setSeoUrlMapper($seoUrlMapper);

        if (!$seoMatch) {
            $this->setExpectedException('Symfony\Component\Routing\Exception\ResourceNotFoundException');
        }
        $result = $matcher->match($url);

        $keys = ['_controller', '_route'];
        if (in_array('seoKey', $return)) {
            $keys[] = 'seoKey';
        }

        $this->assertTrue(is_array($result), 'Result must be an array.');

        // Matcher result should contain controller and route.
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result, "Result array must have '{$key}' key.");
            $this->assertEquals($return[$key], $result[$key], "Result array with '{$key}' key holds wrong value.");
        }
    }

    /**
     * Data provider for testing redirect.
     *
     * @return array
     */
    public function getTestMatchRedirectCaseData()
    {
        $out = [];

        $map = [
            // Should be document name f.e product, content. Product for testing purposes.
            'product' => [
                '_controller' => 'testModelTestHandler',
                '_route' => 'testModelTestRoute',
            ],
        ];

        // Case #0.
        $url1 = new UrlObject();
        $url1->setUrl('seo1/');
        $url1->setKey('url1');
        $document1 = new Product();
        $document1->setUrls(new \ArrayIterator([$url1]));
        $document1->setExpiredUrls([md5(strtolower('seo2/'))]);
        $out[] = ['seo2/', $map, [$document1], 'testModelTestRoute', '/seo1/'];

        // Case #1 (not all urls expired).
        $url2 = new UrlObject();
        $url2->setUrl('seo1/');
        $url2->setKey('url1');
        $document2 = new Product();
        $document2->setUrls(new \ArrayIterator([$url2]));
        $document2->setExpiredUrls([md5(strtolower('seo2/')), md5(strtolower('seo1/'))]);
        $out[] = ['seo2/', $map, [$document2], 'testModelTestRoute', '/seo1/'];

        // Case #2 - lowercase existing seo url.
        $url3 = new UrlObject();
        $url3->setUrl('Seo1/');
        $url3->setKey('url1');
        $document3 = new Product();
        $document3->setUrls(new \ArrayIterator([$url3]));
        $document3->setExpiredUrls([]);
        $out[] = ['seo1/', $map, [$document3], 'testModelTestRoute', '/Seo1/'];

        // Case #3 - trailing slash missing on existing url.
        $url4 = new UrlObject();
        $url4->setUrl('Seo1/');
        $url4->setKey('url1');
        $document4 = new Product();
        $document4->setUrls(new \ArrayIterator([$url4]));
        $document4->setExpiredUrls([]);
        $out[] = ['seo1', $map, [$document4], 'testModelTestRoute', '/Seo1/'];

        // Case #4 - redirect to original url, which is not necessarily the first one.
        $url51 = new UrlObject();
        $url51->setUrl('Seo1/');
        $url51->setKey('url1');
        $url52 = new UrlObject();
        $url52->setUrl('Seo2/');
        $url52->setKey('url2');

        $document5 = new Product();
        $document5->setUrls(new \ArrayIterator([$url51, $url52]));
        $document5->setExpiredUrls([]);
        $out[] = ['seo2/', $map, [$document5], 'testModelTestRoute', '/Seo2/'];

        // Case #5 - expired url.
        $document5 = new Product();
        $document5->setUrls(new \ArrayIterator());
        $document5->setExpiredUrls([md5(strtolower('seo2/')), md5(strtolower('seo1/'))]);
        $out[] = ['seo2/', $map, [$document5], 'testModelTestRoute', '/seo2/', false];

        return $out;
    }

    /**
     * Tests match method redirect case.
     *
     * @param string      $url           Url given to match.
     * @param array       $map           Type map.
     * @param array       $documentsData Array of found documents.
     * @param string      $matchedRoute  Matched route.
     * @param bool|string $redirectTo    String if should redirect to given path, false in other cases.
     * @param null|string $expectedKey   Expected key of the url.
     *
     * @dataProvider getTestMatchRedirectCaseData
     */
    public function testMatchRedirectCase(
        $url,
        $map,
        $documentsData,
        $matchedRoute,
        $redirectTo = false,
        $expectedKey = null
    ) {
        // Matcher setup.
        /** @var RedirectableUrlMatcher|\PHPUnit_Framework_MockObject_MockObject $parentMatcher */
        $parentMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RedirectableUrlMatcher')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'match',
                    'redirect',
                    'getContext',
                ]
            )
            ->getMock();

        $parentMatcher
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue(new RequestContext()));
        $parentMatcher
            ->expects($this->never())
            ->method('match');

        $testReturn = 'test';

        if ($redirectTo) {
            // Should redirect to given url.
            $parentMatcher
                ->expects($this->once())
                ->method('redirect')
                ->with($redirectTo, $matchedRoute, 'http')
                ->will($this->returnValue($testReturn));
        } else {
            // Should not redirect and should let to do all work for parent matcher.
            $parentMatcher
                ->expects($this->never())
                ->method('redirect');
        }

        // Elasticsearch setup.
        $repository = $this
            ->getMockBuilder('ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $repository
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->will($this->returnValue($documentsData));

        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this
            ->getMockBuilder('ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getDocumentMapping'])
            ->getMock();

        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $mapping */
        $mapping = $this->getMockBuilder('ONGR\ElasticsearchBundle\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->setMethods(['getType'])
            ->getMock();

        reset($map);
        $mapping->expects($this->any())
            ->method('getType')
            ->willReturn(key($map));

        $manager
            ->expects($this->any())
            ->method('getDocumentMapping')
            ->willReturn($mapping);

        $manager
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        // Testing redirection.
        $matcher = new SeoUrlMatcher($parentMatcher, $manager, $map);
        $matcher->setSeoUrlMapper($this->getSeoUrlMapperMock($redirectTo, $expectedKey));

        $this->assertEquals($testReturn, $matcher->match($url));
    }

    /**
     * Data provider for testing 404 cases.
     *
     * @return array
     */
    public function getTestMatch404Data()
    {
        $out = [];

        $map = [
            'product' => [
                // Should be document name f.e product, content. Product for testing purposes.
                '_controller' => 'testModelTestHandler',
                '_route' => 'testModelTestRoute',
            ],
        ];

        // Case 1 - document doesn't have urls.
        $document = new Product();
        $document->setUrls([]);
        $document->setExpiredUrls([md5(strtolower('seo2/')), md5(strtolower('seo1/'))]);
        $out[] = ['seo2/', $map, [$document], 'testModelTestRoute', false];

        // Case 2 - document not found.
        $document = null;
        $out[] = ['seo2/', $map, [$document], 'testModelTestRoute', false];

        return $out;
    }

    /**
     * Tests match with 404 status case.
     *
     * @param string      $url           Url given to match.
     * @param array       $map           Type map.
     * @param array       $documentsData Array of found documents.
     * @param string      $matchedRoute  Matched route.
     * @param bool|string $redirectTo    String if should redirect to given path, false in other cases.
     *
     * @dataProvider getTestMatch404Data
     *
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatch404(
        $url,
        $map,
        $documentsData,
        $matchedRoute,
        $redirectTo = false
    ) {
        $this->testMatchRedirectCase($url, $map, $documentsData, $matchedRoute, $redirectTo);
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

        /** @var RedirectableUrlMatcher|\PHPUnit_Framework_MockObject_MockObject $parentMatcher */
        $parentMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RedirectableUrlMatcher')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'match',
                    'redirect',
                    'getContext',
                ]
            )
            ->getMock();
        $parentMatcher
            ->expects($this->never())
            ->method('match');
        $parentMatcher
            ->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));
        $parentMatcher
            ->expects($this->never())
            ->method('redirect');

        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this
            ->getMockBuilder('Elasticsearch\ORM\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $matcher = new SeoUrlMatcher($parentMatcher, $manager, [], false);
        $matcher->setSeoUrlMapper($this->getSeoUrlMapperMock());
        $matcher->match('seo1/');
    }

    /**
     * Tests if exception is thrown without using url mapper.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Seo url mapper is not set.
     */
    public function testGetLinkWithoutUrlMapper()
    {
        /** @var RedirectableUrlMatcher|\PHPUnit_Framework_MockObject_MockObject $parentMatcher */
        $parentMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RedirectableUrlMatcher')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'match',
                    'redirect',
                    'getContext',
                ]
            )
            ->getMock();

        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this
            ->getMockBuilder('Elasticsearch\ORM\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $matcher = new \ReflectionClass('ONGR\RouterBundle\Routing\SeoUrlMatcher');
        $method = $matcher->getMethod('getLink');
        $method->setAccessible(true);
        $method->invokeArgs(new SeoUrlMatcher($parentMatcher, $manager, [], false), [new \stdClass(), '']);
    }

    /**
     * Method to get mock for SeoUrlMapper.
     *
     * @param string $url Url.
     * @param string $key Key.
     *
     * @return SeoUrlMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSeoUrlMapperMock($url = null, $key = null)
    {
        /** @var SeoUrlMapper|\PHPUnit_Framework_MockObject_MockObject $seoUrlMapper */
        $seoUrlMapper = $this->getMock('ONGR\RouterBundle\Service\SeoUrlMapper');
        $seoUrlMapper->expects($this->any())->method('getLinkByKey')->will(
            $this->returnValue(isset($url) ? $url : null)
        );
        $seoUrlMapper->expects($this->any())->method('checkDocumentUrlExists')->will(
            $this->returnValue(isset($key) ? $key : null)
        );

        return $seoUrlMapper;
    }
}
