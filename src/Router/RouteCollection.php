<?php

namespace Bitty\Router;

use Bitty\Collection;
use Bitty\Router\Route;
use Bitty\Router\RouteCollectionInterface;

class RouteCollection extends Collection implements RouteCollectionInterface
{
    /**
     * Route counter.
     *
     * @var int
     */
    protected $routeCounter = 0;

    /**
     * {@inheritDoc}
     */
    public function add(
        $methods,
        $path,
        $callback,
        array $constraints = [],
        $name = null
    ) {
        $route = new Route(
            $methods,
            $path,
            $callback,
            $constraints,
            $name,
            $this->routeCounter++
        );

        if (null === $name) {
            $name = $route->getIdentifier();
        }

        $this->set($name, $route);
    }
}
