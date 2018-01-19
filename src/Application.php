<?php

namespace Bitty;

use Bitty\Container\Container;
use Bitty\Container\ContainerInterface;
use Bitty\EventManager\EventManagerServiceProvider;
use Bitty\Http\RequestServiceProvider;
use Bitty\Http\ResponseServiceProvider;
use Bitty\Http\Server\MiddlewareChain;
use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandlerServiceProvider;
use Bitty\Router\RouterServiceProvider;
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
                new RequestHandlerServiceProvider(),
                new RequestServiceProvider(),
                new ResponseServiceProvider(),
                new RouterServiceProvider(),
            ]
        );

        $this->middleware = new MiddlewareChain($this->container);
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
     * Runs the application.
     */
    public function run()
    {
        $requestHandler = $this->container->get('request_handler');
        $this->middleware->setDefaultHandler($requestHandler);

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

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
