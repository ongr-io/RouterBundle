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

use ONGR\ElasticsearchBundle\ORM\Manager;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Overrides default framework router.
 */
class Router extends BaseRouter
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SeoUrlGenerator|null
     */
    protected $seoGenerator = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ContainerInterface $container,
        $resource,
        array $options = [],
        RequestContext $context = null
    ) {
        $this->container = $container;
        parent::__construct($container, $resource, $options, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatcher()
    {
        $isAjax = $this->context instanceof RequestContext && $this->context->isAjax();

        $seoUrlMatcher = new SeoUrlMatcher(
            parent::getMatcher(),
            $this->getESManager(),
            $this->container->getParameter('ongr_router.seo_route'),
            $isAjax,
            $this->container->getParameter('ongr_router.url_key')
        );
        $seoUrlMatcher->setSeoUrlMapper($this->container->get('ongr_router.seo_url_mapper'));

        return $seoUrlMatcher;
    }

    /**
     * @return SeoUrlGenerator
     */
    public function getGenerator()
    {
        if ($this->seoGenerator !== null) {
            return $this->seoGenerator;
        }

        $typeMap = $this->container->getParameter('ongr_router.seo_route');
        $parentGenerator = parent::getGenerator();
        $path = null;
        $request = $this->getRequest();
        if ($request) {
            $path = $request->getPathInfo();
        }
        $this->seoGenerator = new SeoUrlGenerator($parentGenerator, $typeMap, $path);
        $this->seoGenerator->setContext($this->context);

        return $this->seoGenerator;
    }

    /**
     * Returns header for forcing browser to trust ssl origin.
     *
     * @return array
     */
    public function getSSlOriginTrustHeaders()
    {
        $context = $this->getContext();

        return ['Access-Control-Allow-Origin' => 'https://' . $context->getHost()];
    }

    /**
     * Get request service.
     *
     * @return bool|object
     */
    private function getRequest()
    {
        if ($this->container->isScopeActive('request')) {
            return $this->container->get('request');
        }

        return false;
    }

    /**
     * Returns Elasticsearch manager instance that was set in app/config.yml.
     *
     * @return Manager
     */
    private function getESManager()
    {
        $managerName = $this->container->getParameter('ongr_router.manager');

        return $this
            ->container
            ->get($managerName === 'default' ? 'es.manager' : sprintf('es.manager.%s', $managerName));
    }
}
