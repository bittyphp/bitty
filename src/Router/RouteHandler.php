<?php

namespace Bizurkur\Bitty\Router;

use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\ContainerAwareTrait;
use Bizurkur\Bitty\Http\Exception\NotFoundException;
use Bizurkur\Bitty\Http\Request;
use Bizurkur\Bitty\Http\Response;
use Bizurkur\Bitty\Router\RouteHandlerInterface;
use Bizurkur\Bitty\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteHandler implements RouteHandlerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router = null;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        $path = '/'.ltrim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();

        $route = $this->router->find($path, $method);
        if (false === $route) {
            throw new NotFoundException();
        }

        $callback = $route->getCallback();
        $params = $route->getParams();
        if ($callback instanceof \Closure) {
            $response = $callback($request, $params);
        } else {
            $class = array_shift($callback);
            $action = array_shift($callback);

            $controller = is_object($class) ? $class : new $class();
            if ($controller instanceof ContainerAwareInterface) {
                $controller->setContainer($this->container);
            }

            $response = call_user_func_array(
                [$controller, $action],
                [$request, $params]
            );
        }

        if (is_string($response)) {
            return new Response($response);
        } elseif ($response instanceof ResponseInterface) {
            return $response;
        }

        return new Response();
    }
}
