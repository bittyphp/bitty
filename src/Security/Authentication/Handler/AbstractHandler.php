<?php

namespace Bizurkur\Bitty\Security\Authentication\Handler;

use Bizurkur\Bitty\Security\Authentication\AuthenticatorInterface;
use Bizurkur\Bitty\Security\Authentication\ContextInterface;
use Bizurkur\Bitty\Security\Authentication\Handler\HandlerInterface;
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
