<?php

namespace Bitty\Security\Shield;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerAwareTrait;
use Bitty\Security\Authentication\AuthenticatorInterface;
use Bitty\Security\Authorization\AuthorizerInterface;
use Bitty\Security\Context\ContextInterface;
use Bitty\Security\Shield\ShieldInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractShield implements ShieldInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ContextInterface
     */
    protected $context = null;

    /**
     * @var AuthenticatorInterface
     */
    protected $authenticator = null;

    /**
     * @var AuthorizerInterface
     */
    protected $authorizer = null;

    /**
     * @var mixed[]
     */
    protected $config = null;

    /**
     * @param ContextInterface $context
     * @param AuthenticatorInterface $authenticator
     * @param AuthorizerInterface $authorizer
     * @param mixed[] $config
     */
    public function __construct(
        ContextInterface $context,
        AuthenticatorInterface $authenticator,
        AuthorizerInterface $authorizer,
        array $config = []
    ) {
        $this->context       = $context;
        $this->authenticator = $authenticator;
        $this->authorizer    = $authorizer;
        $this->config        = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function handle(ServerRequestInterface $request);

    /**
     * {@inheritDoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Gets the default config settings for the shield.
     *
     * @return mixed[]
     */
    protected function getDefaultConfig()
    {
        return [];
    }
}
