<?php

namespace Bitty\Security;

use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Security\Handler\HandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityMiddleware implements MiddlewareInterface
{
    /**
     * @var HandlerInterface
     */
    protected $authHandler = null;

    /**
     * @param HandlerInterface $authHandler
     */
    public function __construct(HandlerInterface $authHandler)
    {
        $this->authHandler = $authHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $authResponse = $this->authHandler->handle($request);
        if ($authResponse) {
            return $authResponse;
        }

        return $handler->handle($request);
    }
}
