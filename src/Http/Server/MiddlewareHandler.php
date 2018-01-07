<?php

namespace Bizurkur\Bitty\Http\Server;

use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareHandler implements RequestHandlerInterface
{
    /**
     * @param MiddlewareInterface $middleware
     * @param RequestHandlerInterface $handler
     */
    public function __construct(
        MiddlewareInterface $middleware,
        RequestHandlerInterface $handler
    ) {
        $this->middleware = $middleware;
        $this->handler    = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        return $this->middleware->process($request, $this->handler);
    }
}
