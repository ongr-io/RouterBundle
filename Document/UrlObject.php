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
 * @ES\Object
 */
class UrlObject
{
    /**
     * @var string URL.
     *
     * @ES\Property(name="url", type="string", analyzer="urlAnalyzer")
     */
    public $url;

    /**
     * @var string Key.
     *
     * @ES\Property(name="key", type="string", index="no")
     */
    public $key;
}
