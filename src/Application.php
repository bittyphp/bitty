<?php

namespace Bizurkur\Bitty;

use Bizurkur\Bitty\Container;
use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\ContainerAwareTrait;
use Bizurkur\Bitty\Http\Request;
use Bizurkur\Bitty\Http\Response;
use Bizurkur\Bitty\Http\Server\RequestHandler;
use Bizurkur\Bitty\Router;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ResponseInterface;

class Application implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param PsrContainerInterface|null $container
     */
    public function __construct(PsrContainerInterface $container = null)
    {
        if (null === $container) {
            $this->container = new Container();
        } else {
            $this->container = $container;
        }

        $this->setDefaultServices();
    }

    /**
     * Sets up the default required services.
     *
     * TODO: This should probably be moved to its own class.
     */
    public function setDefaultServices()
    {
        if (!$this->container->has('router')) {
            $router = new Router();
            $this->container->set('router', $router);
        }

        if (!$this->container->has('request_handler')) {
            $requestHandler = new RequestHandler($this->container->get('router'));
            if ($requestHandler instanceof ContainerAwareInterface) {
                $requestHandler->setContainer($this->container);
            }

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
     * Runs the application.
     */
    public function run()
    {
        $request = $this->container->get('request');

        $response = $this->container->get('request_handler')->handle($request);

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

            // TODO: Update this? Remove it?
            // foreach ($this->cookies as $cookie) {
            //     if ($cookie->isRaw()) {
            //         setrawcookie(
            //             $cookie->getName(),
            //             $cookie->getValue(),
            //             $cookie->getExpires(),
            //             $cookie->getPath(),
            //             $cookie->getDomain(),
            //             $cookie->getSecure(),
            //             $cookie->getHttpOnly()
            //         );
            //     } else {
            //         setcookie(
            //             $cookie->getName(),
            //             $cookie->getValue(),
            //             $cookie->getExpires(),
            //             $cookie->getPath(),
            //             $cookie->getDomain(),
            //             $cookie->getSecure(),
            //             $cookie->getHttpOnly()
            //         );
            //     }
            // }
        }

        echo (string) $response->getBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
