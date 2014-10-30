<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext as BaseContext;

/**
 * Extends BaseContext and adds isAjax getter.
 */
class RequestContext extends BaseContext
{
    /**
     * @var bool This request is AJAX.
     */
    protected $isAjax = false;

    /**
     * {@inheritdoc}
     */
    public function fromRequest(Request $request)
    {
        parent::fromRequest($request);

        $this->isAjax = $request->isXmlHttpRequest();
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return $this->isAjax;
    }
}
