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
            // ignore routes that aren't for this request method
            $methods = $route->getMethods();
            if (!empty($methods)
                && !in_array($method, $methods)
            ) {
                continue;
            }

            $pattern = $route->getPath();

            if ($pattern === $path) {
                return $route;
            }

            $constraintPattern = $pattern;
            $constraints = $route->getConstraints();
            foreach ($constraints as $_name => $_pattern) {
                $constraintPattern = str_replace(
                    '{'.$_name.'}',
                    '(?<'.$_name.'>'.$_pattern.')',
                    $constraintPattern
                );
            }

            $matches = [];
            if (preg_match("`^$constraintPattern$`", $path, $matches)) {
                $params = [];
                foreach ($constraints as $_name => $_pattern) {
                    $params[$_name] = isset($matches[$_name]) ? $matches[$_name] : '';
                }
                $route->setParams($params);

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
}
