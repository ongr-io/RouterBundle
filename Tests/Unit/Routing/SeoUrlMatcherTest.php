<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Functional\Routing;

use ONGR\RouterBundle\Document\SeoAwareTrait;
use ONGR\RouterBundle\Routing\SeoUrlMatcher;
use Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

class SeoUrlMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestMatchData()
    {
        /** @var SeoAwareTrait $object */
        $object = new \stdClass();
        // Should be document name f.e product, content. stdclass for testing purposes.
        $testDocument = 'stdclass';

        $url1 = new \stdClass();
        $url1->url = 'test/';
        $url1->key = 'test_key';

        $object->expiredUrl = [];
        $object->url = new \ArrayIterator([$url1]);

        /** @var SeoAwareTrait $expiredUrlObject */
        $expiredUrlObject = new \stdClass(null);
        $expiredUrlObject->expiredUrl = [md5('test/')];

        $url2 = new \stdClass();
        $url2->url = 'test2/';
        $expiredUrlObject->url = new \ArrayIterator([$url2]);

        return [
            // Case #0.
            [[], [], 'test/', false, false],
            [
                [
                    $testDocument => [
                        '_controller' => 'testModelTestHandler',
                        '_route' => 'testModelTestRoute',
                    ],
                ],
                [$object],
                'test/',
                true,
                true,
                [
                    '_controller' => 'testModelTestHandler',
                    '_route' => 'testModelTestRoute',
                    'seoKey' => 'test_key',
                ],
            ],
            // Case #1.
            [
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
                [$expiredUrlObject, $object],
                'test/',
                true,
                true,
                [
                    '_controller' => 'testModelTestHandler',
                    '_route' => 'testModelTestRoute',
                    'seoKey' => 'test_key',
                ],
            ],
            // Case #2.
            [
                [
                    $testDocument => [
                        '_controller' => 'testModelTestHandler',
                        '_route' => 'testModelTestRoute',
                    ],
                ],
                [$object],
                '',
                false,
                false,
            ],
            // Case #3.
            [
                [
                    $testDocument => [
                        '_controller' => 'testModelTestHandler',
                        '_route' => 'testModelTestRoute',
                    ],
                ],
                [$object],
                '/',
                false,
                false,
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
        $parentMatcher = $this->getMock(
            'Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface',
            [
                'match',
                'redirect',
                'getContext',
            ]
        );
        $parentMatcher
            ->expects($this->never())
            ->method('redirect');
        $parentMatcher
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue(new RequestContext()));

        if ($seoMatch) {
            $parentMatcher
                ->expects($this->once())
                ->method('match')
                ->will($this->throwException(new ResourceNotFoundException()));
        } else {
            $return = [
                '_controller' => 'test',
                '_route' => 'test',
            ];
            // Must call parent matcher once if no documents found.
            $parentMatcher
                ->expects($this->once())
                ->method('match')
                ->will($this->returnValue($return));
        }

        // Elasticsearch setup.
        $repository = $this
            ->getMockBuilder('ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $manager = $this
            ->getMockBuilder('ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository'])
            ->getMock();

        if ($seek) {
            $repository
                ->expects($this->once())
                ->method('execute')
                ->will($this->returnValue($documentsData));

            $manager
                ->expects($this->once())
                ->method('getRepository')
                ->will($this->returnValue($repository));
        } else {
            $manager->expects($this->never())->method('getRepository');
        }

        // Matcher testing.
        $matcher = new SeoUrlMatcher($parentMatcher, $manager, $map);

        // Mocking SeoUrlMapper.
        $seoUrlMapper = $this->getSeoUrlMapperMock($url, isset($return['seoKey']) ? $return['seoKey'] : null);
        $matcher->setSeoUrlMapper($seoUrlMapper);

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
            // Should be document name f.e product, content. stdclass for testing purposes.
            'stdclass' => [
                '_controller' => 'testModelTestHandler',
                '_route' => 'testModelTestRoute',
            ]
        ];

        // Case #0.
        $url1 = new \stdClass();
        $url1->url = 'seo1/';
        $url1->key = 'url1';
        $document1 = new \stdClass(null);
        $document1->url = new \ArrayIterator([$url1]);
        $document1->expiredUrl = [md5(strtolower('seo2/'))];
        $out[] = ['seo2/', $map, [$document1], 'testModelTestRoute', '/seo1/'];

        // Case #1 (not all urls expired).
        $url2 = new \stdClass();
        $url2->url = 'seo1/';
        $url2->key = 'url1';
        $document2 = new \stdClass(null);
        $document2->url = new \ArrayIterator([$url2]);
        $document2->expiredUrl = [md5(strtolower('seo2/')), md5(strtolower('seo1/'))];
        $out[] = ['seo2/', $map, [$document2], 'testModelTestRoute', '/seo1/'];

        // Case #2 - lowercase existing seo url.
        $url3 = new \stdClass();
        $url3->url = 'Seo1/';
        $url3->key = 'url1';
        $document3 = new \stdClass(null);
        $document3->url = new \ArrayIterator([$url3]);
        $document3->expiredUrl = [];
        $out[] = ['seo1/', $map, [$document3], 'testModelTestRoute', '/Seo1/'];

        // Case #3 - trailing slash missing on existing url.
        $url4 = new \stdClass();
        $url4->url = 'Seo1/';
        $url4->key = 'url1';
        $document4 = new \stdClass(null);
        $document4->url = new \ArrayIterator([$url4]);
        $document4->expiredUrl = [];
        $out[] = ['seo1', $map, [$document4], 'testModelTestRoute', '/Seo1/'];

        // Case #4 - redirect to original url, which is not necessarily the first one.
        $url51 = new \stdClass();
        $url51->url = 'Seo1/';
        $url51->key = 'url1';
        $url52 = new \stdClass();
        $url52->url = 'Seo2/';
        $url52->key = 'url2';

        $document5 = new \stdClass(null);
        $document5->url = new \ArrayIterator([$url51, $url52]);
        $document5->expiredUrl = [];
        $out[] = ['seo2/', $map, [$document5], 'testModelTestRoute', '/Seo2/'];

        // Case #5 - expired url.
        $document5 = new \stdClass(null);
        $document5->url = new \ArrayIterator();
        $document5->expiredUrl = [md5(strtolower('seo2/')), md5(strtolower('seo1/'))];
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
        $parentMatcher = $this->getMock(
            'Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface',
            [
                'match',
                'redirect',
                'getContext',
            ]
        );

        $parentMatcher
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue(new RequestContext()));
        $parentMatcher
            ->expects($this->once())
            ->method('match')
            ->will($this->throwException(new ResourceNotFoundException()));

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

        $manager = $this
            ->getMockBuilder('ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository'])
            ->getMock();

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
            'stdclass' => [
                // Should be document name f.e product, content. stdclass for testing purposes.
                '_controller' => 'testModelTestHandler',
                '_route' => 'testModelTestRoute',
            ]
        ];

        // Case 1 - document doesn't have urls.
        $document = new \stdClass(null);
        $document->url = [];
        $document->expiredUrl = [md5(strtolower('seo2/')), md5(strtolower('seo1/'))];
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
        $parentMatcher = $this->getMock(
            'Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface',
            [
                'match',
                'redirect',
                'getContext',
            ]
        );
        $parentMatcher
            ->expects($this->once())
            ->method('match')
            ->will($this->throwException(new ResourceNotFoundException()));
        $parentMatcher
            ->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));
        $parentMatcher
            ->expects($this->never())
            ->method('redirect');

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
        $parentMatcher = $this->getMock(
            'Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface',
            [
                'match',
                'redirect',
                'getContext',
            ]
        );

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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSeoUrlMapperMock($url = null, $key = null)
    {
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
