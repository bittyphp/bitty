<?php

namespace Bitty\Security\Handler;

use Bitty\Security\Authentication\AuthenticatorInterface;
use Bitty\Security\Context\ContextInterface;
use Bitty\Security\Handler\HandlerInterface;
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
}
