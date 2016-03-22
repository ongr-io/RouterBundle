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
use ONGR\RouterBundle\Document\SeoAwareInterface;
use Symfony\Cmf\Component\Routing\ProviderBasedGenerator;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

class DocumentUrlGenerator extends ProviderBasedGenerator implements VersatileGeneratorInterface
{
    /**
     * @var array Route map configuration to map Elasticsearch types and Controllers.
     */
    private $routeMap;

    /**
     * @var MetadataCollector
     */
    private $collector;

    /**
     * @return mixed
     */
    public function getRouteMap()
    {
        return $this->routeMap;
    }

    /**
     * @param mixed $routeMap
     */
    public function setRouteMap($routeMap)
    {
        $this->routeMap = $routeMap;
    }

    /**
     * @return MetadataCollector
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @param MetadataCollector $collector
     */
    public function setCollector(MetadataCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        try {
            if ($name instanceof SeoAwareInterface) {
                $documentUrl = $name->getUrl();
            } else {
                throw new RouteNotFoundException();
            }

            $type = $this->collector->getDocumentType(get_class($name));
            $route = new Route(
                $documentUrl,
                [
                    '_controller' => $this->routeMap[$type],
                    'document' => $name,
                    'type' => $type,
                ]
            );

            // the Route has a cache of its own and is not recompiled as long as it does not get modified
            $compiledRoute = $route->compile();
            $hostTokens = $compiledRoute->getHostTokens();

            $debug_message = $this->getRouteDebugMessage($name);

            return $this->doGenerate(
                $compiledRoute->getVariables(),
                $route->getDefaults(),
                $route->getRequirements(),
                $compiledRoute->getTokens(),
                $parameters,
                $debug_message,
                $referenceType,
                $hostTokens
            );
        } catch (\Exception $e) {
            throw new RouteNotFoundException('Document is not correct or route cannot be generated.');
        }
    }
    /**
     * @param mixed $name The route "name" which may also be an object or anything
     *
     * @return bool
     * @throws RouteNotFoundException
     */
    public function supports($name)
    {
        if ($name instanceof SeoAwareInterface) {
            return true;
        } else {
            throw new RouteNotFoundException('$name must be an instance of SeoAwareInterface');
        }
    }
    /**
     * @param mixed $name
     * @param array $parameters which should contain a content field containing
     *                          a RouteReferrersReadInterface object
     *
     * @return string
     */
    public function getRouteDebugMessage($name, array $parameters = array())
    {
        if ($name instanceof SeoAwareInterface) {
            return 'The route object is fit for parsing to generate() method';
        } else {
            return 'Given route object must be an instance of SeoAwareInterface';
        }
    }
}
