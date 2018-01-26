<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Adds a new route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     */
    public function add(
        $methods,
        $path,
        $callback,
        array $constraints = [],
        $name = null
    );

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
     * Finds a route for the given request.
     *
     * @param ServerRequestInterface $request
     *
     * @return RouteInterface
     *
     * @throws NotFoundException When unable to find a route.
     */
    public function find(ServerRequestInterface $request);

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
