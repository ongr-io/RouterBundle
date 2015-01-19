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

use ONGR\RouterBundle\Document\UrlObject;
use ONGR\RouterBundle\Routing\SeoUrlGenerator;
use ONGR\RouterBundle\Tests\app\fixture\Acme\TestBundle\Document\Product;
use Symfony\Component\Routing\RequestContext;

/**
 * Tests for url generator class.
 */
class SeoUrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Getter and setter test.
     */
    public function testSetGetContext()
    {
        $parent = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            [
                'generate',
                'setContext',
                'getContext',
            ]
        );

        $generator = new SeoUrlGenerator($parent, []);

        $context = new RequestContext();
        $hash = spl_object_hash($context);

        $generator->setContext($context);

        $this->assertEquals($hash, spl_object_hash($generator->getContext()));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function getGenerateTestCases()
    {
        $out = [];

        // Case #0: should match.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $document = new Product();
        $url01 = new UrlObject();
        $url01->setUrl('test/url');

        $document->setUrls(new \ArrayIterator([$url01]));
        $parameters = ['document' => $document];
        $expect = 'http://localhost/test/url';
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #1: should match with GET parameter.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $document = new Product();
        $url11 = new UrlObject();
        $url11->setUrl('test/url');

        $document->setUrls(new \ArrayIterator([$url11]));
        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = 'http://localhost/test/url?test=test';
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #2: should match (forceDefault should be added to url string).
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $document = new Product();
        $url21 = new UrlObject();
        $url21->setUrl('test/url');

        $document->setUrls(new \ArrayIterator([$url21]));
        $parameters = [
            'document' => $document,
            'test' => 'test',
            'forceDefault' => true,
        ];
        $expect = 'http://localhost/test/url?test=test&forceDefault=1';
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #3: should match second url.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $document = new Product();
        $url31 = new UrlObject();
        $url31->setUrl('test/url');
        $url32 = new UrlObject();
        $url32->setUrl('base/path/file.htm');

        $document->setUrls(new \ArrayIterator([$url31, $url32]));
        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = 'http://localhost/test/url?test=test';
        $out[] = [$typeMap, $name, $parameters, $expect, false, false];

        // Case #4: shouldn't match.
        $name = 'ongr_test_document_page';
        $typeMap = [];

        $document = new Product();
        $url41 = new UrlObject();
        $url41->setUrl('test/url');

        $document->setUrls(new \ArrayIterator([$url41]));
        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = false;
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #5: should try to generate default link.
        $name = 'ongr_test_document_page';
        $typeMap = [
            'TestModel' => [
                '_route' => $name,
                '_controller' => 'ONGRTestBundle:Test:document',
                '_default_route' => 'testdefaultroute',
                '_id_param' => 'testId',
            ],
        ];

        $document = new Product();
        $document->id = 'testid';
        $document->setUrls([]);

        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = false;
        $parentController = 'testdefaultroute';
        $parentParameters = [
            'testId' => $document->id,
            'test' => 'test',
        ];
        $out[] = [$typeMap, $name, $parameters, $expect, $parentController, $parentParameters];

        // Case #6: should not try to generate default link.
        list($name, $typeMap) = $this->getDefaultTypeMap();
        $document = new Product();
        $document->id = 'testid';
        $document->setUrls([]);
        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = false;
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #7: when requesting specific url with key, that url should be generated.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $url71 = new UrlObject();
        $url71->setUrl('test/url0');
        $url72 = new UrlObject();
        $url72->setUrl('test/url1');
        $url72->setKey('url1');
        $url73 = new UrlObject();
        $url73->setUrl('test/url2');
        $url73->setKey('url2');

        $document = new Product();
        $document->setUrls(new \ArrayIterator([$url71, $url72, $url73]));
        $parameters = [
            'document' => $document,
            '_seo_key' => 'url2',
            'test' => 'test',
        ];
        $expect = 'http://localhost/test/url2?test=test';
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #8: when requesting specific url with key that does not exist, default url should be generated.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $url81 = new UrlObject();
        $url81->setUrl('test/url1');
        $url81->setKey('url1');
        $url82 = new UrlObject();
        $url82->setUrl('test/url2');
        $url82->setKey('url2');

        $document = new Product();
        $document->setUrls(new \ArrayIterator([$url81, $url82]));
        $parameters = [
            'document' => $document,
            '_seo_key' => 'url3',
            'test' => 'test',
        ];
        $expect = 'http://localhost/test/url1?test=test';
        $out[] = [$typeMap, $name, $parameters, $expect];

        return $out;
    }

    /**
     * Tests generate method.
     *
     * @param array       $typeMap     Model to route mapping.
     * @param string      $name        Route name.
     * @param array       $parameters  Parameters.
     * @param bool|string $expect      Expected url or false if should go to parent matcher.
     * @param string|bool $pController Controller.
     * @param array|bool  $pParameters Parameters.
     *
     * @dataProvider getGenerateTestCases()
     */
    public function testGenerate(
        $typeMap,
        $name,
        $parameters,
        $expect,
        $pController = false,
        $pParameters = false
    ) {
        $parent = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            [
                'generate',
                'setContext',
                'getContext',
            ]
        );
        $generator = new SeoUrlGenerator($parent, $typeMap);
        $context = new RequestContext();
        $generator->setContext($context);

        if ($expect) {
            $parent->expects($this->never())->method('generate');
            $this->assertEquals($expect, $generator->generate($name, $parameters, true));
        } else {
            if ($pController) {
                $parent->expects($this->once())->method('generate')->with($pController, $pParameters, true)
                    ->will($this->returnValue('test'));
            } else {
                $parent->expects($this->once())->method('generate')->with($name, $parameters, true)
                    ->will($this->returnValue('test'));
            }
            $this->assertEquals('test', $generator->generate($name, $parameters, true));
        }
    }

    /**
     * @return array
     */
    private function getDefaultTypeMap()
    {
        $name = 'ongr_test_document_page';
        $typeMap = [
            'TestModel' => [
                '_route' => $name,
                '_controller' => 'ONGRTestBundle:Test:document',
            ],
        ];

        return [$name, $typeMap];
    }
}
