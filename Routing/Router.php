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

use ONGR\ElasticsearchBundle\Service\Manager;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Overrides default framework router.
 */
class Router extends BaseRouter implements VersatileParameterGeneratorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SeoUrlGenerator|null
     */
    private $seoGenerator = null;

    /**
     * @var Manager
     */
    private $manager;

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
            $this->getManager(),
            $this->getContainer()->getParameter('ongr_router.seo_route'),
            $isAjax,
            $this->getContainer()->getParameter('ongr_router.seo_key')
        );
        $seoUrlMatcher->setSeoUrlMapper($this->getContainer()->get('ongr_router.seo_url_mapper'));

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

        $typeMap = $this->getContainer()->getParameter('ongr_router.seo_route');
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
        if ($this->getContainer()->isScopeActive('request')) {
            return $this->getContainer()->get('request');
        }

        return false;
    }

    /**
     * Returns Elasticsearch manager instance that was set in app/config.yml.
     *
     * @return Manager
     */
    private function getManager()
    {
        return $this->manager;
    }

    /**
     * @param Manager $manager
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
