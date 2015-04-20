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

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * Trait that adds SEO fields to document with nested urls field.
 */
trait SeoAwareNestedTrait
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
     * @var UrlNested[]|\Iterator
     *
     * @ES\Property(name="urls", type="nested", objectName="ONGRRouterBundle:UrlNested", multiple=true)
     */
    protected $urls = [];

    /**
     * @var string[] Array of expired url hashes.
     *
     * @ES\Property(name="expired_urls", type="string")
     */
    protected $expiredUrls = [];

    /**
     * @return \Iterator|UrlNested[]
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @param \Iterator|UrlNested[] $urls
     */
    public function setUrls($urls)
    {
        $this->urls = $urls;
    }

    /**
     * @return string[]
     */
    public function getExpiredUrls()
    {
        return $this->expiredUrls;
    }

    /**
     * @param string[] $expiredUrls
     */
    public function setExpiredUrls($expiredUrls)
    {
        $this->expiredUrls = $expiredUrls;
    }
}
