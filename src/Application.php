<?php

namespace Bitty;

use Bitty\Container\Container;
use Bitty\Container\ContainerInterface;
use Bitty\EventManager\EventManager;
use Bitty\Http\Request;
use Bitty\Http\Response;
use Bitty\Http\Server\MiddlewareChain;
use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandler;
use Bitty\Router\Router;
use Bitty\Security\Authentication\AuthenticatorProxy;
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

        $this->middleware = new MiddlewareChain($this->container);

        $this->setDefaultServices();
    }

    /**
     * Gets the container.
     *
     * @return ContainerInterface
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
     * Sets up the default required services.
     *
     * TODO: This should probably be moved to its own class.
     */
    protected function setDefaultServices()
    {
        if (!$this->container->has('router')) {
            $router = new Router();
            $this->container->set('router', $router);
        }

        if (!$this->container->has('request_handler')) {
            $requestHandler = new RequestHandler($this->container->get('router'));
            $this->container->set('request_handler', $requestHandler);
        }

        if (!$this->container->has('request')) {
            $request = Request::createFromGlobals();
            $this->container->set('request', $request);
        }

        if (!$this->container->has('response')) {
            $response = new Response();
            $this->container->set('response', $response);
        }

        if (!$this->container->has('event_manager')) {
            $eventManager = new EventManager();
            $this->container->set('event_manager', $eventManager);
        }
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
