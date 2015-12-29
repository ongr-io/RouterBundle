<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\app\fixture\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Dummy test controller.
 */
class TestController extends Controller
{
    /**
     * Action that already receives document and SEO key.
     *
     * @param object $document Document.
     *
     * @return JsonResponse
     */
    public function documentAction($document)
    {
        $response = new JsonResponse();
        $response->setData(['id' => $document->id]);

        return $response;
    }
}
