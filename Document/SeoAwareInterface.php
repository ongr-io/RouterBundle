<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Document;

/**
 * Interface SeoAwareInterface.
 */
interface SeoAwareInterface
{
    /**
     * Structure that represents possible URLs for the model.
     *
     * Eg.:
     *
     * <code>
     * array(
     *     array('url' => 'foo/'),
     *     array('url' => 'bar/', 'key' => 'bar_url'),
     * )
     * </code>
     *
     * @return UrlObject[]|\Iterator
     */
    public function getUrls();

    /**
     * @param \Iterator|UrlObject[] $urls
     */
    public function setUrls($urls);

    /**
     * Array of expired url hashes.
     *
     * @return string[]
     */
    public function getExpiredUrls();

    /**
     * @param string[] $expiredUrls
     */
    public function setExpiredUrls($expiredUrls);
}
