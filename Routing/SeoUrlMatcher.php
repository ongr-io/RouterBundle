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

use ONGR\ElasticsearchBundle\DSL\Query\MatchQuery;
use ONGR\ElasticsearchBundle\DSL\Query\TermQuery;
use ONGR\ElasticsearchBundle\DSL\Search;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\RouterBundle\Document\SeoAwareTrait;
use ONGR\RouterBundle\Service\SeoUrlMapper;
use Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * URL matcher with extended functionality for document matching.
 */
class SeoUrlMatcher extends RedirectableUrlMatcher
{
    const SCHEME_HTTP = 'http';

    /**
     * @var RedirectableUrlMatcher
     */
    protected $cachedMatcher = null;

    /**
     * @var Manager
     */
    protected $esManager = null;

    /**
     * @var array
     */
    protected $typeMap = null;

    /**
     * @var bool
     */
    protected $allowHttps = false;

    /**
     * @var SeoUrlMapper
     */
    protected $seoUrlMapper;

    /**
     * @param RedirectableUrlMatcher $parentMatcher Parent matcher that is called when this matcher fails.
     * @param Manager                $esManager     ES manager.
     * @param array                  $typeMap       Type map.
     * @param bool                   $allowHttps    Is https allowed.
     */
    public function __construct($parentMatcher, $esManager, $typeMap, $allowHttps = false)
    {
        $this->cachedMatcher = $parentMatcher;
        $this->esManager = $esManager;
        $this->typeMap = array_change_key_case($typeMap, CASE_LOWER);
        $this->allowHttps = $allowHttps;
    }

    /**
     * Sets SEO URL mapper.
     *
     * @param SeoUrlMapper $seoUrlMapper SEO URL mapper.
     */
    public function setSeoUrlMapper($seoUrlMapper)
    {
        $this->seoUrlMapper = $seoUrlMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        $url = ltrim(rawurldecode($pathinfo), '/');
        $requestedUrlOriginal = $url;

        /** @var RequestContext $context */
        $context = $this->getCachedMatcher()->getContext();

        // Try to load default sf route first.
        try {
            return $this->getCachedMatcher()->match($pathinfo);
        } catch (ResourceNotFoundException $e) {
            // Default sf route not found.
            if (!$this->allowHttps && $context->getScheme() != self::SCHEME_HTTP) {
                throw new ResourceNotFoundException('Non-http urls are not processed', $e->getCode(), $e);
            }

            // Add trailing slash.
            $url = preg_replace('/^([a-zA-Z0-9\-\/]+[^\/])$/', '$1/', $url);
            $result = $this->getDocumentByUrl($url);

            if ($result !== null) {
                /** @var SeoAwareTrait $document */
                list($documentName, $document) = $result;
                list($documentSeoKey, $documentLink) = $this->getLink($document, $url);

                // Url doesn't exist.
                $urls = $document->getUrls();
                if (empty($urls) && $documentLink === false) {
                    throw $e;
                }

                if (is_array($document->getExpiredUrls())
                    && in_array($this->getUrlHash($url), $document->getExpiredUrls())
                ) {
                    if ($documentSeoKey === false) {
                        return $this->doRedirect(
                            $this->ensurePrefixSlash($documentLink),
                            $this->getTypeMap()[$documentName]['_route'],
                            (!$this->allowHttps) ? self::SCHEME_HTTP : null
                        );
                    }
                }

                // Force redirect to original link, if links are not identical (lowercase, trailing slash).
                if ($requestedUrlOriginal !== $documentLink) {
                    return $this->doRedirect($documentLink, $this->getTypeMap()[$documentName]['_route']);
                }

                return array_merge(
                    $this->getTypeMap()[$documentName],
                    [
                        'document' => $document,
                        'seoKey' => $documentSeoKey,
                    ]
                );
            }

            throw $e;
        }
    }

    /**
     * Cached matcher redirect.
     *
     * @param string $link   URL.
     * @param string $route  Route name.
     * @param string $scheme Scheme to use. E.g. 'http' or 'https'.
     *
     * @return array
     */
    protected function doRedirect($link, $route, $scheme = self::SCHEME_HTTP)
    {
        return $this->getCachedMatcher()->redirect($this->ensurePrefixSlash($link), $route, $scheme);
    }

    /**
     * Hacky solution to get seo key and url.
     *
     * @param SeoAwareTrait $document Document.
     * @param string        $url      Url.
     *
     * @return array
     * @throws \LogicException
     */
    protected function getLink($document, $url)
    {
        if (!$this->getSeoUrlMapper()) {
            throw new \LogicException('Seo url mapper is not set.');
        }

        $documentSeoKey = $this->getSeoUrlMapper()->checkDocumentUrlExists($document, $url);
        $documentLink = $this->getSeoUrlMapper()->getLinkByKey($document, $documentSeoKey);

        return [$documentSeoKey, $documentLink];
    }

    /**
     * Generates hash for given url.
     *
     * @param string $url URL string.
     *
     * @return string
     */
    protected function getUrlHash($url)
    {
        return md5(strtolower($url));
    }

    /**
     * Returns search instance that is used to search for documents in Elasticsearch.
     *
     * @param string $url URL.
     *
     * @return Search
     */
    protected function getSearch($url)
    {
        $search = new Search();

        if ($url) {
            $search
                ->addQuery(new MatchQuery($url, 'urls.url'), 'should')
                ->addQuery(new TermQuery('expired_urls', $this->getUrlHash($url)), 'should');
        }

        return $search;
    }

    /**
     * Returns document by URL.
     *
     * @param string $url URL.
     *
     * @return array|null
     * @throws \Exception Search type not found.
     */
    private function getDocumentByUrl($url)
    {
        $repository = $this->getEsManager()->getRepository([]);
        $out = [];

        /** @var SeoAwareTrait $document */
        foreach ($repository->execute($this->getSearch($url)) as $document) {
            $documentName = strtolower(preg_replace('/.*([\w]+)$/U', '$1', get_class($document)));

            if ($document && isset($this->getTypeMap()[$documentName])) {
                $out = [$documentName, $document];
                if (!in_array($this->getUrlHash($url), $document->getExpiredUrls())) {
                    break;
                }
            }
        }

        return empty($out) ? null : $out;
    }

    /**
     * Helper method to ensure that link has slash prefix.
     *
     * @param string $link Link.
     *
     * @return string Link with slash prefix.
     */
    private function ensurePrefixSlash($link)
    {
        if (substr($link, 0, 1) != '/') {
            $link = '/' . $link;
        }

        return $link;
    }

    /**
     * @return SeoUrlMapper
     */
    public function getSeoUrlMapper()
    {
        return $this->seoUrlMapper;
    }

    /**
     * @return array
     */
    public function getTypeMap()
    {
        return $this->typeMap;
    }

    /**
     * @return Manager
     */
    public function getEsManager()
    {
        return $this->esManager;
    }

    /**
     * @return RedirectableUrlMatcher
     */
    public function getCachedMatcher()
    {
        return $this->cachedMatcher;
    }
}
