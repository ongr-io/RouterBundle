<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\app\fixture\Acme\TestBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\Document\DocumentTrait;
use ONGR\RouterBundle\Document\SeoAwareTrait;

/**
 * Dummy test product for SEO testing.
 *
 * @ES\Document(type="product")
 */
class Product implements DocumentInterface
{
    use DocumentTrait;
    use SeoAwareTrait;
}
