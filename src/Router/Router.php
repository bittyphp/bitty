<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    /**
     * @var RouteCollectionInterface
     */
    protected $routes = null;

    /**
     * @var RouteMatcherInterface
     */
    protected $matcher = null;

    /**
     * @param RouteCollectionInterface $routes
     * @param RouteMatcherInterface $matcher
     */
    public function __construct(RouteCollectionInterface $routes, RouteMatcherInterface $matcher)
    {
        $this->routes  = $routes;
        $this->matcher = $matcher;
    }

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
        $this->routes->add($methods, $path, $callback, $constraints, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function has($name)
    {
        return $this->routes->has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        $route = $this->routes->get($name);
        if ($route) {
            return $route;
        }

        throw new NotFoundException(sprintf('No route named "%s" exists.', $name));
    }

    /**
     * {@inheritDoc}
     */
    public function find(ServerRequestInterface $request)
    {
        return $this->matcher->match($request);
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
