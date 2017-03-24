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

use ONGR\ElasticsearchBundle\Mapping\MetadataCollector;
use ONGR\ElasticsearchBundle\Result\DocumentIterator;
use ONGR\ElasticsearchBundle\Result\ObjectIterator;
use ONGR\ElasticsearchBundle\Service\Manager;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\RouterBundle\Document\SeoAwareInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ElasticsearchRouteProvider implements RouteProviderInterface
{
    /**
     * @var array Route map configuration to map Elasticsearch types and Controllers.
     */
    private $routeMap;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var MetadataCollector
     */
    private $collector;

    /**
     * ElasticsearchRouteProvider constructor.
     *
     * @param MetadataCollector $collector
     * @param array $routeMap
     */
    public function __construct($collector, array $routeMap = [])
    {
        $this->collector = $collector;
        $this->routeMap = $routeMap;
    }

    /**
     * Returns Elasticsearch manager instance that was set in app/config.yml.
     *
     * @return Manager
     */
    public function getManager()
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
     * @return array
     */
    public function getRouteMap()
    {
        return $this->routeMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        if (!$this->manager) {
            throw new ParameterNotFoundException(
                'Manager must be set to execute query to the elasticsearch'
            );
        }

        $routeCollection = new RouteCollection();
        $requestPath = $request->getPathInfo();

        $search = new Search();
        $search->addQuery(new MatchQuery('url', $requestPath), BoolQuery::FILTER);

        $results = $this->manager->search(array_keys($this->routeMap), $search->toArray());
        #TODO Clean up this place.
        $results = new DocumentIterator($results, $this->manager);
        try {
            /** @var SeoAwareInterface $document */
            foreach ($results as $document) {
                $type = $this->collector->getDocumentType(get_class($document));
                if (isset($this->routeMap[$type])) {
                    $route = new Route(
                        $document->getUrl(),
                        [
                            '_controller' => $this->routeMap[$type],
                            'document' => $document,
                            'type' => $type,
                        ]
                    );

                    $routeCollection->add('ongr_route_' . $route->getDefault('type'), $route);
                } else {
                    throw new RouteNotFoundException(sprintf('Route for type %s% cannot be generated.', $type));
                }
            }
        } catch (\Exception $e) {
            throw new RouteNotFoundException('Document is not correct or route cannot be generated.');
        }

        return $routeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name)
    {
        throw new RouteNotFoundException('Dynamic provider generates routes on the fly.');
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names)
    {
        // Returns empty Route collection.
        return new RouteCollection();
    }
}
