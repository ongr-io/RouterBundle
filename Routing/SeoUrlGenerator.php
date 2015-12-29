<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Routing;

use ONGR\RouterBundle\Document\UrlNested;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Provides SEO URL generator.
 */
class SeoUrlGenerator extends UrlGenerator
{
    /**
     * @var UrlGeneratorInterface
     */
    private $parentGenerator = null;

    /**
     * @var array Type map.
     */
    private $typeMap = null;

    /**
     * @param UrlGeneratorInterface $parent Parent generator.
     * @param array                 $config Type map.
     */
    public function __construct($parent, $config = [])
    {
        $this->parentGenerator = $parent;
        $this->typeMap = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $absolute = false)
    {
        foreach ($this->typeMap as $params) {
            if ($params['_route'] == $name) {
                $document = $parameters['document'];
                $key = null;
                if (isset($parameters['_seo_key'])) {
                    $key = $parameters['_seo_key'];
                    unset($parameters['_seo_key']);
                }

                $link = self::getLinkByDocument($document, $key);

                if (!$link) {
                    if (isset($params['_default_route'])) {
                        unset($parameters['document']);
                        $parameters[$params['_id_param']] = $document->getId();

                        return $this->parentGenerator->generate($params['_default_route'], $parameters, $absolute);
                    }

                    return $this->parentGenerator->generate($name, $parameters, $absolute);
                }

                unset($parameters['document']);

                if (substr($link, 0, 1) != '/') {
                    $link = '/' . $link;
                }

                return $this->doGenerate(
                    [],
                    ['_controller' => $params['_controller']],
                    [],
                    [['text', $link]],
                    $parameters,
                    $name,
                    $absolute,
                    []
                );
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * Returns URL for a document.
     *
     * @param object $document Document.
     * @param string $key      Optional URL key.
     *
     * @return string|null
     */
    public static function getLinkByDocument($document, $key = null)
    {
        $urls = $document->urls;

        if (!count($urls)) {
            return null;
        }

        if ($key !== null) {
            $keyUrl = null;
            /** @var UrlNested $url */
            foreach ($urls as $url) {
                $urlKeyValue = $url->key;
                if (!empty($urlKeyValue) && $urlKeyValue === $key) {
                    $keyUrl = $url->url;
                    break;
                }
            }
            if ($keyUrl !== null) {
                return $keyUrl;
            }
        }

        $urls->rewind();
        /** @var UrlNested $currentUrl */
        $currentUrl = $urls->current();

        return $currentUrl->url;
    }
}
