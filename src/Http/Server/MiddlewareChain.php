<?php

namespace Bizurkur\Bitty\Http\Server;

use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\ContainerInterface;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareChain implements RequestHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @var MiddlewareInterface[]
     */
    protected $chain = [];

    /**
     * @var RequestHandlerInterface
     */
    protected $defaultHandler = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Adds middleware to the chain.
     *
     * @param MiddlewareInterface $middleware
     */
    public function add(MiddlewareInterface $middleware)
    {
        if ($middleware instanceof ContainerAwareInterface) {
            $middleware->setContainer($this->container);
        }

        $this->chain[] = $middleware;
    }

    /**
     * Sets the default request handler.
     *
     * @param RequestHandlerInterface $defaultHandler
     */
    public function setDefaultHandler(RequestHandlerInterface $defaultHandler)
    {
        if ($defaultHandler instanceof ContainerAwareInterface) {
            $defaultHandler->setContainer($this->container);
        }

        $this->defaultHandler = $defaultHandler;
    }

    /**
     * Gets the default request handler.
     *
     * @return RequestHandlerInterface|null
     */
    public function getDefaultHandler()
    {
        return $this->defaultHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        $chain = $this->buildChain();

        return $chain->handle($request);
    }

    /**
     * Builds the request handler chain.
     *
     * @return RequestHandlerInterface
     */
    protected function buildChain()
    {
        $chain = $this->defaultHandler;

        foreach (array_reverse($this->chain) as $middleware) {
            $chain = new MiddlewareHandler($middleware, $chain);
        }

        return $chain;
    }
}
