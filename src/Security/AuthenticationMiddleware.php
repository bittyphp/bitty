<?php

namespace Bizurkur\Bitty\Security;

use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\ContainerAwareTrait;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Security\Authentication\Handler\AbstractHandler;
use Bizurkur\Bitty\Security\Authentication\Handler\HandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware implements MiddlewareInterface, ContainerAwareInterface
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
