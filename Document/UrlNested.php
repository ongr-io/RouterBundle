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
 * Object for storing url / key pair.
 *
 * @ES\Nested
 */
class UrlNested
{
    /**
     * @var string URL.
     *
     * @ES\Property(name="url", type="string", analyzer="urlAnalyzer")
     */
    protected $url;

    /**
     * @var string Key.
     *
     * @ES\Property(name="key", type="string", index="not_analyzed")
     */
    protected $key;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}
