<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\app\fixture\Acme\TestBundle\Controller;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Dummy test controller.
 */
class TestController extends Controller
{
    /**
     * Action to get document by id.
     *
     * @param string $documentId Document id.
     *
     * @return JsonResponse
     */
    public function testIdAction($documentId)
    {
        $response = new JsonResponse();
        $response->setData(['document_id' => $documentId]);

        return $response;
    }

    /**
     * Action that already receives document and SEO key.
     *
     * @param DocumentInterface $document Document.
     * @param string            $seoKey   Seo key.
     *
     * @return JsonResponse
     */
    public function testDocumentAction($document, $seoKey)
    {
        $response = new JsonResponse();
        $response->setData(['document_id' => $document->getId(), 'seo_key' => $seoKey]);

        return $response;
    }

    /**
     * Action for default router.
     */
    public function testDefaultAction()
    {
        return new JsonResponse(['symfony_router' => true]);
    }
}
