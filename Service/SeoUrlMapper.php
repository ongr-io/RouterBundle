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

use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * Searches URLs in document object.
 */
class SeoUrlMapper
{
    /**
     * Method to get url by specified key.
     *
     * @param DocumentInterface $document Document.
     * @param string            $key      Url key.
     *
     * @return string|bool
     */
    public function getLinkByKey($document, $key)
    {
        if (!empty($document->url)) {
            foreach ($document->url as $url) {
                if ($url->key === $key) {
                    return $url->url;
                }
            }
        }

        return false;
    }

    /**
     * Searches document for url in case insensitive manner according to supplied url.
     *
     * @param DocumentInterface $document     Document.
     * @param string            $requestedUrl Requested url string.
     *
     * @return string|bool Document key or false if such not found.
     */
    public function checkDocumentUrlExists($document, $requestedUrl)
    {
        if (!empty($document->url)) {
            $requestedUrlLowercased = mb_strtolower($requestedUrl, 'UTF-8');

            foreach ($document->url as $url) {
                if ($requestedUrlLowercased === mb_strtolower($url->url, 'UTF-8')) {
                    return $url->key;
                }
            }

            $document->url->rewind();

            return $document->url->current()->key;
        }

        return false;
    }
}
