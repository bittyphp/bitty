<?php

namespace Bitty\Security\Handler;

use Bitty\Security\Authentication\AuthenticationInterface;
use Bitty\Security\Context\ContextInterface;
use Bitty\Security\Handler\HandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var AuthenticationInterface
     */
    protected $authentication = null;

    /**
     * @var ContextInterface
     */
    protected $context = null;

    /**
     * @param AuthenticationInterface $authentication
     * @param ContextInterface $context
     */
    public function __construct(AuthenticationInterface $authentication, ContextInterface $context)
    {
        $this->authentication = $authentication;
        $this->context        = $context;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function handle(ServerRequestInterface $request);
}
