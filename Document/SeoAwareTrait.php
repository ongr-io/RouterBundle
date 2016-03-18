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

use ONGR\ElasticsearchBundle\Annotation;

/**
 * Trait that adds SEO fields to document.
 */
trait SeoAwareTrait
{
    /**
     * @var string URL.
     *
     * @Annotation\Property(name="url", type="string", options={"analyzer"="urlAnalyzer"})
     */
    public $url;
}
