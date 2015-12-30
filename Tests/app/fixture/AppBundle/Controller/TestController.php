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

use ONGR\ElasticsearchBundle\Tests\app\fixture\Acme\BarBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dummy test controller.
 */
class TestController extends Controller
{

    /**
     * Action that returns 'OK'
     *
     * @return JsonResponse
     */
    public function homeAction()
    {
        return new Response('OK');
    }

    /**
     * Action that already receives document and SEO key.
     *
     * @param Product $document Document.
     *
     * @return JsonResponse
     */
    public function documentAction($document)
    {
        return new Response($document->title);
    }
}
