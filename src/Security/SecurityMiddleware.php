<?php

namespace Bitty\Security;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerAwareTrait;
use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Security\Handler\AbstractHandler;
use Bitty\Security\Handler\HandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

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
        if ($this->authHandler instanceof AbstractHandler) {
            $authenticator = $this->authHandler->getAuthenticator();
            $this->container->get('authenticator')->setAuthenticator($authenticator);
        }

        $authResponse = $this->authHandler->handle($request);
        if ($authResponse) {
            return $authResponse;
        }

        return $handler->handle($request);
    }
}
