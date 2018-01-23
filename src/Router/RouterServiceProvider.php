<?php

namespace Bitty\Router;

use Bitty\Router\RouteCollection;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteMatcher;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\Router;
use Bitty\Router\RouterInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class RouterServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getFactories()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensions()
    {
        return [
            'route.collection' => function (ContainerInterface $container, RouteCollectionInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                return new RouteCollection();
            },
            'route.matcher' => function (ContainerInterface $container, RouteMatcherInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                $routes = $container->get('route.collection');

                return new RouteMatcher($routes);
            },
            'router' => function (ContainerInterface $container, RouterInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                $routes  = $container->get('route.collection');
                $matcher = $container->get('route.matcher');

                return new Router($routes, $matcher);
            },
        ];
    }
}
