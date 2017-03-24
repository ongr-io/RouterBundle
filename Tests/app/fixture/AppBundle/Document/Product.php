<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\app\fixture\AppBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\RouterBundle\Document\SeoAwareInterface;
use ONGR\RouterBundle\Document\SeoAwareTrait;

/**
 * Dummy test product for SEO testing.
 *
 * @ES\Document(type="product")
 */
class Product implements SeoAwareInterface
{
    use SeoAwareTrait;

    /**
     * @ES\Id()
     */
    public $id;

    /**
     * @ES\Property(type="text")
     */
    public $title;
}
