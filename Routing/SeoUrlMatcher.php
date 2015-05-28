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

use ONGR\ElasticsearchBundle\DSL\Bool\Bool;
use ONGR\ElasticsearchBundle\DSL\Query\MatchQuery;
use ONGR\ElasticsearchBundle\DSL\Query\NestedQuery;
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
     * @var string
     */
    protected $urlKey = null;

    /**
     * @var SeoUrlMapper
     */
    protected $seoUrlMapper;

    /**
     * @param RedirectableUrlMatcher $parentMatcher Parent matcher that is called when this matcher fails.
     * @param Manager                $esManager     ES manager.
     * @param array                  $typeMap       Type map.
     * @param bool                   $allowHttps    Is https allowed.
     * @param string                 $urlKey        Url key to search routes with.
     */
    public function __construct($parentMatcher, $esManager, $typeMap, $allowHttps = false, $urlKey = null)
    {
        $this->cachedMatcher = $parentMatcher;
        $this->esManager = $esManager;
        $this->typeMap = array_change_key_case($typeMap, CASE_LOWER);
        $this->allowHttps = $allowHttps;
        $this->urlKey = $urlKey;
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

            // Check for _no_document_pattern fallback.
            $url = rawurldecode($pathinfo);
            foreach ($this->getTypeMap() as $typeMap) {
                if (empty($typeMap['_no_document_patterns'])) {
                    continue;
                }

                foreach ($typeMap['_no_document_patterns'] as $pattern) {
                    $pattern = "~{$pattern}~";
                    if (preg_match($pattern, $url)) {
                        return array_merge($typeMap, ['document' => null, 'seoKey' => null]);
                    }
                }
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

        if (!$url) {
            return $search;
        }

        $bool = new Bool();
        $bool->addToBool(new MatchQuery('urls.url', $url));

        if ($this->urlKey != null) {
            $bool->addToBool(new TermQuery('urls.key', $this->urlKey));
        }

        $search->addQuery(new NestedQuery('urls', $bool), 'should');
        $search->addQuery(new TermQuery('expired_urls', $this->getUrlHash($url)), 'should');

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
            $mapping = $this->getEsManager()->getDocumentMapping($document);
            $type = $mapping->getType();

            if ($document && isset($this->getTypeMap()[$type])) {
                $out = [$type, $document];
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
