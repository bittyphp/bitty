<?php

namespace Bizurkur\Bitty;

use Bizurkur\Bitty\Router\Exception\NotFoundException;
use Bizurkur\Bitty\Router\Route;
use Bizurkur\Bitty\RouterInterface;

class Router implements RouterInterface
{
    /**
     * List of route data.
     *
     * @var mixed[]
     */
    protected $routes = [];

    /**
     * Route counter.
     *
     * @var int
     */
    protected $routeCounter = 0;

    /**
     * Adds a new route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param callback $callback
     * @param string[] $constraints
     * @param string|null $name
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

        $this->routes[$name] = $route;
    }

    /**
     * Removes a route.
     *
     * @param string $name Name of the route.
     */
    public function remove($name)
    {
        if (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has($name)
    {
        return isset($this->routes[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        throw new NotFoundException(sprintf('No route named "%s" exists.', $name));
    }

    /**
     * {@inheritDoc}
     */
    public function find($path, $method)
    {
        foreach ($this->routes as $route) {
            if (!$this->isMethodMatch($route, $method)) {
                continue;
            }

            if ($this->isPathMatch($route, $path)) {
                return $route;
            }
        }

        throw new NotFoundException();
    }

    /**
     * {@inheritDoc}
     */
    public function generateUri($name, array $params = [])
    {
        $route = $this->get($name);

        $path = $route->getPath();
        foreach ($params as $id => $value) {
            $path = str_replace('{'.$id.'}', (string) $value, $path);
        }

        return $path;
    }

    /**
     * Checks if the route matches the request method.
     *
     * @param Route $route
     * @param string $method
     *
     * @return bool
     */
    protected function isMethodMatch(Route $route, $method)
    {
        $methods = $route->getMethods();
        if ([] === $methods) {
            // any method allowed
            return true;
        }

        return in_array($method, $methods);
    }

    /**
     * Checks if the route matches the request path.
     *
     * @param Route $route
     * @param string $path
     *
     * @return bool
     */
    protected function isPathMatch(Route $route, $path)
    {
        $pattern = $route->getPattern();
        if ($pattern === $path) {
            return true;
        }

        $matches = [];
        if (!preg_match("`^$pattern$`", $path, $matches)) {
            return false;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        $route->setParams($params);

        return true;
    }
}
