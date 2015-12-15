<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Service;

use ONGR\RouterBundle\Document\SeoAwareTrait;
use ONGR\RouterBundle\Document\UrlObject;

/**
 * Searches URLs in document object.
 */
class SeoUrlMapper
{
    /**
     * Method to get url by specified key.
     *
     * @param SeoAwareTrait $document Document.
     * @param string        $key      Url key.
     *
     * @return string|bool
     */
    public function getLinkByKey($document, $key)
    {
        $urls = $document->getUrls();
        /** @var UrlObject $url */
        foreach ($urls as $url) {
            if ($url->getKey() === $key) {
                return $url->getUrl();
            }
        }

        return false;
    }

    /**
     * Searches document for url in case insensitive manner according to supplied url.
     *
     * @param SeoAwareTrait $document     Document.
     * @param string        $requestedUrl Requested url string.
     *
     * @return string|bool Document key or false if such not found.
     */
    public function checkDocumentUrlExists($document, $requestedUrl)
    {
        $urls = $document->getUrls();
        if (count($urls)) {
            $requestedUrlLowercased = mb_strtolower($requestedUrl, 'UTF-8');

            /** @var UrlObject $url */
            foreach ($urls as $url) {
                if ($requestedUrlLowercased === mb_strtolower($url->getUrl(), 'UTF-8')) {
                    return $url->getKey();
                }
            }

            $urls->rewind();
            /** @var UrlObject $currentUrl */
            $currentUrl = $urls->current();

            return $currentUrl->getKey();
        }

        return false;
    }
}
