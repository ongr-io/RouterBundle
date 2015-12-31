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
use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchBundle\Service\Manager;
use ONGR\ElasticsearchDSL\Query\MatchQuery;
use ONGR\ElasticsearchDSL\Search;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ElasticsearchRouteProvider implements RouteProviderInterface
{
    /**
     * @var array Route map configuration to map Elasticsearch types and Controllers
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
     * @param array             $routeMap
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
     * @inheritDoc
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        if (!$this->manager) {
            throw new \Exception('Manager must be set to execute query to the elasticsearch');
        }

        $routeCollection =  new RouteCollection();
        $requestPath = $request->getPathInfo();

        $search = new Search();
        $search->addQuery(new MatchQuery('url', $requestPath));

        $results = $this->manager->execute(array_keys($this->routeMap), $search, Result::RESULTS_OBJECT);

        foreach ($results as $document) {
            if ($route = $this->getRouteFromDocument($document)) {
                $routeCollection->add('ongr_route_'.$route->getDefault('type'), $route);
            }
        }

        return $routeCollection;
    }

    /**
     * @inheritDoc
     */
    public function getRouteByName($name)
    {
        $args = func_get_args();
        $parameters = $args[1];
        return $this->getRouteFromDocument($parameters['document']);
    }

    /**
     * Resolvs route from document object.
     *
     * @param $document
     *
     * @return Route
     */
    private function getRouteFromDocument($document)
    {
        try {
            $type = $this->collector->getDocumentType(get_class($document));

            if (array_key_exists($type, $this->routeMap)) {
                $route = new Route(
                    $document->url,
                    [
                        '_controller' => $this->routeMap[$type],
                        'document' => $document,
                        'type' => $type,
                    ]
                );

                return $route;
            } else {
                throw new RouteNotFoundException(sprintf('Route for type %s% cannot be generated.', $type));
            }
        } catch (\Exception $e) {
            throw new RouteNotFoundException('Document is not correct or route cannot be generated.');
        }
    }

    /**
     * @inheritDoc
     */
    public function getRoutesByNames($names)
    {
        // Returns empty Route collection.
        return new RouteCollection();
    }
}
