<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit\Routing;

use ONGR\RouterBundle\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Request;

class RequestContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests overwritten request context class isAjax method.
     */
    public function testIsAjax()
    {
        $request = new Request([], [], [], [], [], ['HTTP_X_Requested_With' => 'XMLHttpRequest']);
        $context = new RequestContext();
        $context->fromRequest($request);

        $this->assertTrue($context->isAjax());
    }
}
