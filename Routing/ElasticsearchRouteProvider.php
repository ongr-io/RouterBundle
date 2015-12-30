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
     * ElasticsearchRouteProvider constructor.
     *
     * @param array $routeMap
     */
    public function __construct(array $routeMap = [])
    {
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

        $collection =  new RouteCollection();
        $requestPath = $request->getPathInfo();

        $search = new Search();
        $search->addQuery(new MatchQuery('url', $requestPath));

        $results = $this->manager->execute(array_keys($this->routeMap), $search, Result::RESULTS_OBJECT);

        foreach ($results as $result) {
            //Boom, you are doomed!
        }
    }

    /**
     * @inheritDoc
     */
    public function getRouteByName($name)
    {
        // TODO: Implement getRouteByName() method.
    }

    /**
     * @inheritDoc
     */
    public function getRoutesByNames($names)
    {
        // TODO: Implement getRoutesByNames() method.
    }
}
