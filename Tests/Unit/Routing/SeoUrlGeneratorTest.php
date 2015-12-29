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

use ONGR\RouterBundle\Document\UrlNested;
use ONGR\RouterBundle\Routing\SeoUrlGenerator;
use ONGR\RouterBundle\Tests\app\fixture\Acme\TestBundle\Document\Product;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Constraints\Url;

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
        $url01 = new UrlNested();
        $url01->url = 'test/url';

        $document->urls = new \ArrayIterator([$url01]);
        $parameters = ['document' => $document];
        $expect = 'http://localhost/test/url';
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #1: should match with GET parameter.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $document = new Product();
        $url11 = new UrlNested();
        $url11->url = 'test/url';

        $document->urls = new \ArrayIterator([$url11]);
        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = 'http://localhost/test/url?test=test';
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #2: should match (forceDefault should be added to url string).
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $document = new Product();
        $url21 = new UrlNested();
        $url21->url = 'test/url';

        $document->urls = new \ArrayIterator([$url21]);
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
        $url31 = new UrlNested();
        $url31->url = 'test/url';
        $url32 = new UrlNested();
        $url32->url = 'base/path/file.htm';

        $document->urls = new \ArrayIterator([$url31, $url32]);
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
        $url41 = new UrlNested();
        $url41->url = 'test/url';

        $document->urls = new \ArrayIterator([$url41]);
        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = false;
        $out[] = [$typeMap, $name, $parameters, $expect, false, false, true];

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
        $document->urls = [];

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
        $document->urls = [];
        $parameters = [
            'document' => $document,
            'test' => 'test',
        ];
        $expect = false;
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #7: when requesting specific url with key, that url should be generated.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $url71 = new UrlNested();
        $url71->url = 'test/url0';
        $url72 = new UrlNested();
        $url72->url = 'test/url1';
        $url72->key = 'url1';
        $url73 = new UrlNested();
        $url73->url = 'test/url2';
        $url73->key = 'url2';

        $document = new Product();
        $document->urls = new \ArrayIterator([$url71, $url72, $url73]);
        $parameters = [
            'document' => $document,
            '_seo_key' => 'url2',
            'test' => 'test',
        ];
        $expect = 'http://localhost/test/url2?test=test';
        $out[] = [$typeMap, $name, $parameters, $expect];

        // Case #8: when requesting specific url with key that does not exist, default url should be generated.
        list($name, $typeMap) = $this->getDefaultTypeMap();

        $url81 = new UrlNested();
        $url81->url = 'test/url1';
        $url81->key = 'url1';
        $url82 = new UrlNested();
        $url82->url = 'test/url2';
        $url82->key = 'url2';

        $document = new Product();
        $document->urls = new \ArrayIterator([$url81, $url82]);
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
     * @param bool        $exception   Should throw exception.
     *
     * @dataProvider getGenerateTestCases()
     */
    public function testGenerate(
        $typeMap,
        $name,
        $parameters,
        $expect,
        $pController = false,
        $pParameters = false,
        $exception = false
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
        } elseif ($exception) {
            $this->setExpectedException('Symfony\Component\Routing\Exception\RouteNotFoundException');
            $generator->generate($name, $parameters, true);
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
