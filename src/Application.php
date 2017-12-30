<?php

namespace Bizurkur\Bitty;

use Bizurkur\Bitty\Container;
use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\ContainerInterface;
use Bizurkur\Bitty\Http\Request;
use Bizurkur\Bitty\Http\Response;
use Bizurkur\Bitty\Http\Server\RequestHandler;
use Bizurkur\Bitty\Router;
use Psr\Http\Message\ResponseInterface;

class Application
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

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
     * Runs the application.
     */
    public function run()
    {
        $requestHandler = $this->container->get('request_handler');
        if ($requestHandler instanceof ContainerAwareInterface) {
            $requestHandler->setContainer($this->container);
        }

        $request  = $this->container->get('request');
        $response = $requestHandler->handle($request);

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
