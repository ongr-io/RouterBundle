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

use ArrayObject;
use Symfony\Cmf\Component\Routing\ChainRouter as CmfChainRouter;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Class ChainRouter.
 */
class ChainRouter extends CmfChainRouter
{
    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $absolute = false)
    {
        $debug = new \ArrayObject();
        foreach ($this->all() as $router) {
            if (!$this->isRouterCapable($name, $parameters, $router)) {
                continue;
            }

            $route = $this->tryRouter($name, $parameters, $absolute, $router, $debug);
            if ($route !== null) {
                return $route;
            }
        }

        if ($debug->count()) {
            $debug = array_unique($debug->getArrayCopy());
            $info = implode(', ', $debug);
        } else {
            $info = $this->getErrorMessage($name);
        }

        throw new RouteNotFoundException(sprintf('None of the chained routers were able to generate route: %s', $info));
    }

    /**
     * Tries to generate url using provided router.
     *
     * @param string      $name
     * @param array       $parameters
     * @param bool        $absolute
     * @param mixed       $router
     * @param ArrayObject $debug
     *
     * @return null|string Generated url or null on fail.
     */
    protected function tryRouter($name, $parameters, $absolute, $router, ArrayObject $debug)
    {
        try {
            return $router->generate($name, $parameters, $absolute);
        } catch (RouteNotFoundException $e) {
            $hint = $this->getErrorMessage($name, $router, $parameters);
            $debug[] = $hint;
            if ($this->logger) {
                $this->logger->debug(
                    'Router ' . get_class($router) .
                    " was unable to generate route. Reason: '$hint': " . $e->getMessage()
                );
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param object $router
     *
     * @return bool
     */
    protected function isRouterCapable($name, $parameters, $router)
    {
        if ($name && !is_string($name) && !$router instanceof VersatileGeneratorInterface) {
            return false;
        }
        // If $router is versatile and doesn't support this route name, continue.
        if ($router instanceof VersatileGeneratorInterface && !$router->supports($name)) {
            return false;
        }

        foreach ($parameters as $parameter) {
            if ($parameter !== null
                && !is_scalar($parameter)
                && !($router instanceof VersatileParameterGeneratorInterface)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|object $name
     * @param object        $router
     * @param array         $parameters
     *
     * @return string
     */
    protected function getErrorMessage($name, $router = null, $parameters = [])
    {
        if ($router instanceof VersatileGeneratorInterface) {
            $displayName = $router->getRouteDebugMessage($name, $parameters);
        } elseif (is_object($name)) {
            $displayName = method_exists($name, '__toString') ? (string)$name : get_class($name);
        } else {
            $displayName = (string)$name;
        }

        return "Route '$displayName' not found";
    }
}
