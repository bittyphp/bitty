<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Router\RouteMatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteMatcher implements RouteMatcherInterface
{
    /**
     * @var RouteCollectionInterface
     */
    protected $routes = null;

    /**
     * @param RouteCollectionInterface $routes
     */
    public function __construct(RouteCollectionInterface $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritDoc}
     */
    public function match(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $path   = '/'.ltrim($request->getUri()->getPath(), '/');

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
     * Checks if the route matches the request method.
     *
     * @param RouteInterface $route
     * @param string $method
     *
     * @return bool
     */
    protected function isMethodMatch(RouteInterface $route, $method)
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
     * @param RouteInterface $route
     * @param string $path
     *
     * @return bool
     */
    protected function isPathMatch(RouteInterface $route, $path)
    {
        $pattern = $route->getPattern();
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
