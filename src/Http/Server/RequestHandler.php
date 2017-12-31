<?php

namespace Bizurkur\Bitty\Http\Server;

use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\ContainerAwareTrait;
use Bizurkur\Bitty\Http\Exception\InternalServerErrorException;
use Bizurkur\Bitty\Http\Exception\NotFoundException;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandler implements RequestHandlerInterface, ContainerAwareInterface
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
        $path   = '/'.ltrim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();

        $route = $this->router->find($path, $method);
        if (false === $route) {
            throw new NotFoundException();
        }

        $callback = $route->getCallback();
        $params   = $route->getParams();

        return $this->triggerCallback($callback, $request, $params);
    }

    /**
     * Triggers the callback, passing in the request and parameters.
     *
     * @param callback $callback
     * @param ServerRequestInterface $request
     * @param array $params
     *
     * @return ResponseInterface
     */
    protected function triggerCallback($callback, ServerRequestInterface $request, array $params)
    {
        if ($callback instanceof \Closure) {
            return $callback($request, $params);
        }

        if (is_object($callback) && method_exists($callback, '__invoke')) {
            $this->applyContainerIfAware($callback);

            return $callback($request, $params);
        }

        if (is_array($callback)) {
            $class  = array_shift($callback);
            $action = array_shift($callback);

            $controller = is_object($class) ? $class : new $class();
            $this->applyContainerIfAware($controller);

            return call_user_func_array(
                [$controller, $action],
                [$request, $params]
            );
        }

        throw new InternalServerErrorException();
    }

    /**
     * Sets the container on the callback, if it's container aware.
     *
     * @param callback $callback
     */
    protected function applyContainerIfAware($callback)
    {
        if ($callback instanceof ContainerAwareInterface) {
            $callback->setContainer($this->container);
        }
    }
}
