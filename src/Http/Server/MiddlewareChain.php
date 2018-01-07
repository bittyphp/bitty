<?php

namespace Bizurkur\Bitty\Http\Server;

use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareChain implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    protected $chain = [];

    /**
     * Adds middleware to the chain.
     *
     * @param MiddlewareInterface $middleware
     */
    public function add(MiddlewareInterface $middleware)
    {
        $this->chain[] = $middleware;
    }

    /**
     * Sets the default request handler.
     *
     * @param RequestHandlerInterface $handler
     */
    public function setDefaultHandler(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Gets the default request handler.
     *
     * @return RequestHandlerInterface|null
     */
    public function getDefaultHandler()
    {
        return $this->handler;
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
        $chain = $this->handler;

        foreach (array_reverse($this->chain) as $middleware) {
            $chain = new MiddlewareHandler($middleware, $chain);
        }

        return $chain;
    }
}
