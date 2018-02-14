<?php

namespace Bitty;

use Bitty\Application\EventManagerServiceProvider;
use Bitty\Application\RequestServiceProvider;
use Bitty\Application\RouterServiceProvider;
use Bitty\Container\Container;
use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerInterface;
use Bitty\Middleware\MiddlewareChain;
use Bitty\Middleware\MiddlewareInterface;
use Bitty\Router\RouteInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ResponseInterface;

class Application
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @var MiddlewareChain
     */
    protected $middleware = [];

    /**
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        if (null === $container) {
            $this->container = new Container();
        } else {
            $this->container = $container;
        }

        $this->container->register(
            [
                new EventManagerServiceProvider(),
                new RequestServiceProvider(),
                new RouterServiceProvider(),
            ]
        );

        $this->middleware = new MiddlewareChain();
    }

    /**
     * Gets the container.
     *
     * @return PsrContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Adds middleware to the application.
     *
     * @param MiddlewareInterface $middleware
     */
    public function add(MiddlewareInterface $middleware)
    {
        if ($middleware instanceof ContainerAwareInterface) {
            $middleware->setContainer($this->container);
        }

        $this->middleware->add($middleware);
    }

    /**
     * Registers a list of service providers.
     *
     * @param ServiceProviderInterface[] $providers
     */
    public function register(array $providers)
    {
        $this->container->register($providers);
    }

    /**
     * Adds a GET route.
     *
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function get($path, $callback, array $constraints = [], $name = null)
    {
        return $this->map('GET', $path, $callback, $constraints, $name);
    }

    /**
     * Adds a POST route.
     *
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function post($path, $callback, array $constraints = [], $name = null)
    {
        return $this->map('POST', $path, $callback, $constraints, $name);
    }

    /**
     * Adds a PUT route.
     *
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function put($path, $callback, array $constraints = [], $name = null)
    {
        return $this->map('PUT', $path, $callback, $constraints, $name);
    }

    /**
     * Adds a PATCH route.
     *
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function patch($path, $callback, array $constraints = [], $name = null)
    {
        return $this->map('PATCH', $path, $callback, $constraints, $name);
    }

    /**
     * Adds a DELETE route.
     *
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function delete($path, $callback, array $constraints = [], $name = null)
    {
        return $this->map('DELETE', $path, $callback, $constraints, $name);
    }

    /**
     * Adds an OPTIONS route.
     *
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function options($path, $callback, array $constraints = [], $name = null)
    {
        return $this->map('OPTIONS', $path, $callback, $constraints, $name);
    }

    /**
     * Maps a route to a specific callback.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     *
     * @return RouteInterface
     */
    public function map($methods, $path, $callback, array $constraints = [], $name = null)
    {
        $routes = $this->container->get('route.collection');

        return $routes->add($methods, $path, $callback, $constraints, $name);
    }

    /**
     * Runs the application.
     */
    public function run()
    {
        $routeHandler = $this->container->get('route.handler');
        if ($routeHandler instanceof ContainerAwareInterface) {
            $routeHandler->setContainer($this->container);
        }
        $this->middleware->setDefaultHandler($routeHandler);

        $request  = $this->container->get('request');
        $response = $this->middleware->handle($request);

        $this->sendResponse($response);
    }

    /**
     * Sends the response to the client.
     *
     * @param ResponseInterface $response
     */
    protected function sendResponse(ResponseInterface $response)
    {
        if (!headers_sent()) {
            header(
                sprintf(
                    'HTTP/%s %s %s',
                    $response->getProtocolVersion(),
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                ),
                true
            );

            foreach ($response->getHeaders() as $header => $values) {
                foreach ($values as $value) {
                    header(sprintf("%s: %s", $header, $value), false);
                }
            }
        }

        echo (string) $response->getBody();
    }
}
