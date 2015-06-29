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

use ONGR\ElasticsearchBundle\Document\AbstractDocument;

/**
 * Basic document with seo fields to simplify common Seo usage.
 */
abstract class AbstractSeoDocument extends AbstractDocument implements SeoAwareInterface
{
    use SeoAwareTrait;
}
