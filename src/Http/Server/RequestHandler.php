<?php

namespace Bitty\Http\Server;

use Bitty\Http\Exception\NotFoundException;
use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\Exception\NotFoundException as RouteNotFoundException;
use Bitty\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var RouterInterface
     */
    protected $router = null;

    /**
     * @var CallbackBuilderInterface
     */
    protected $builder = null;

    /**
     * @param RouterInterface $router
     * @param CallbackBuilderInterface $builder
     */
    public function __construct(RouterInterface $router, CallbackBuilderInterface $builder)
    {
        $this->router  = $router;
        $this->builder = $builder;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        try {
            $route = $this->router->find($request);
        } catch (RouteNotFoundException $exception) {
            throw new NotFoundException();
        }

        $callback = $route->getCallback();
        $params   = $route->getParams();

        return $this->triggerCallback($callback, $request, $params);
    }

    /**
     * Triggers the callback, passing in the request and parameters.
     *
     * @param callable|string $callback
     * @param ServerRequestInterface $request
     * @param array $params
     *
     * @return ResponseInterface
     */
    protected function triggerCallback($callback, ServerRequestInterface $request, array $params)
    {
        list($controller, $action) = $this->builder->build($callback);

        if (null !== $action) {
            return call_user_func_array(
                [$controller, $action],
                [$request, $params]
            );
        }

        return $controller($request, $params);
    }
}
