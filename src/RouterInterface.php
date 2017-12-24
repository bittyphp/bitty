<?php

namespace Bizurkur\Bitty;

use Bizurkur\Bitty\Router\Exception\NotFoundException;
use Bizurkur\Bitty\Router\RouteInterface;

interface RouterInterface
{
    /**
     * Checks if a route exists.
     *
     * @param string $name Name of the route.
     *
     * @return bool
     */
    public function has($name);

    /**
     * Gets a route.
     *
     * @param string $name Name of the route.
     *
     * @return RouteInterface
     *
     * @throws NotFoundException When route does not exist.
     */
    public function get($name);

    /**
     * Finds a route for a given path.
     *
     * @param string $path URI path to find route for.
     * @param string $method The request method being used.
     *
     * @return RouteInterface
     *
     * @throws NotFoundException When unable to find a route.
     */
    public function find($path, $method);

    /**
     * Generates a URI for a named route.
     *
     * @param string $name Name of the route.
     * @param mixed[] $params Key/value array of parameters to use.
     *
     * @return string
     *
     * @throws NotFoundException When unable to find route.
     */
    public function generateUri($name, array $params = []);
}
