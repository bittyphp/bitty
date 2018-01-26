<?php

namespace Bitty\Router;

use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\RouterInterface;
use Bitty\Router\UriGeneratorInterface;
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
     * @var UriGeneratorInterface
     */
    protected $uriGenerator = null;

    /**
     * @param RouteCollectionInterface $routes
     * @param RouteMatcherInterface $matcher
     * @param UriGeneratorInterface $uriGenerator
     */
    public function __construct(
        RouteCollectionInterface $routes,
        RouteMatcherInterface $matcher,
        UriGeneratorInterface $uriGenerator
    ) {
        $this->routes       = $routes;
        $this->matcher      = $matcher;
        $this->uriGenerator = $uriGenerator;
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
        return $this->routes->get($name);
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
    public function generateUri(
        $name,
        array $params = [],
        $type = UriGeneratorInterface::ABSOLUTE_PATH
    ) {
        return $this->uriGenerator->generate($name, $params, $type);
    }
}
