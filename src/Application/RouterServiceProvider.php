<?php

namespace Bitty\Application;

use Bitty\Middleware\RequestHandlerInterface;
use Bitty\Router\CallbackBuilder;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\RouteCollection;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteHandler;
use Bitty\Router\RouteMatcher;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\Router;
use Bitty\Router\RouterInterface;
use Bitty\Router\UriGenerator;
use Bitty\Router\UriGeneratorInterface;
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
            'route.collection' => function (
                ContainerInterface $container,
                RouteCollectionInterface $previous = null
            ) {
                if ($previous) {
                    return $previous;
                }

                return new RouteCollection();
            },
            'route.matcher' => function (
                ContainerInterface $container,
                RouteMatcherInterface $previous = null
            ) {
                if ($previous) {
                    return $previous;
                }

                $routes = $container->get('route.collection');

                return new RouteMatcher($routes);
            },
            'route.callback.builder' => function (
                ContainerInterface $container,
                CallbackBuilderInterface $previous = null
            ) {
                if ($previous) {
                    return $previous;
                }

                return new CallbackBuilder($container);
            },
            'uri.generator' => function (
                ContainerInterface $container,
                UriGeneratorInterface $previous = null
            ) {
                if ($previous) {
                    return $previous;
                }

                $routes = $container->get('route.collection');
                $domain = '';
                if ($container->has('uri.domain')) {
                    $domain = $container->get('uri.domain');
                }

                return new UriGenerator($routes, $domain);
            },
            'router' => function (
                ContainerInterface $container,
                RouterInterface $previous = null
            ) {
                if ($previous) {
                    return $previous;
                }

                $routes    = $container->get('route.collection');
                $matcher   = $container->get('route.matcher');
                $generator = $container->get('uri.generator');

                return new Router($routes, $matcher, $generator);
            },
            'route.handler' => function (ContainerInterface $container, RequestHandlerInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                $router  = $container->get('router');
                $builder = $container->get('route.callback.builder');

                return new RouteHandler($router, $builder);
            },
        ];
    }
}
