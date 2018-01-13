<?php

namespace Bitty\Security\Authentication\Handler;

use Bitty\Security\Authentication\AuthenticatorInterface;
use Bitty\Security\Authentication\ContextInterface;
use Bitty\Security\Authentication\Handler\HandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var AuthenticatorInterface
     */
    protected $authenticator = null;

    /**
     * @var ContextInterface
     */
    protected $context = null;

    /**
     * @param AuthenticatorInterface $authenticator
     * @param ContextInterface $context
     */
    public function __construct(AuthenticatorInterface $authenticator, ContextInterface $context)
    {
        $this->authenticator = $authenticator;
        $this->context       = $context;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function handle(ServerRequestInterface $request);

    /**
     * Gets the authenticator.
     *
     * @return AuthenticatorInterface
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * Gets the authentication context.
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
