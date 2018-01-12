<?php

namespace Bizurkur\Bitty\Security;

use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Security\Authentication\Handler\HandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware implements MiddlewareInterface
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
