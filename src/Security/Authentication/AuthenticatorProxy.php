<?php

namespace Bizurkur\Bitty\Security\Authentication;

use Bizurkur\Bitty\Security\Authentication\AuthenticatorInterface;

class AuthenticatorProxy implements AuthenticatorInterface
{
    /**
     * @var AuthenticatorInterface
     */
    protected $authenticator = null;

    /**
     * @param AuthenticatorInterface|null $authenticator
     */
    public function __construct(AuthenticatorInterface $authenticator = null)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Sets which authenticator to use.
     *
     * @param AuthenticatorInterface $authenticator
     */
    public function setAuthenticator(AuthenticatorInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Gets which authenticator to use.
     *
     * @return AuthenticatorInterface
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password, $remember = false)
    {
        if (!$this->authenticator) {
            return false;
        }

        return $this->authenticator->authenticate($username, $password, $remember);
    }

    /**
     * {@inheritDoc}
     */
    public function deauthenticate()
    {
        if (!$this->authenticator) {
            return false;
        }

        return $this->authenticator->deauthenticate();
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthenticated()
    {
        if (!$this->authenticator) {
            return false;
        }

        return $this->authenticator->isAuthenticated();
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {
        if (!$this->authenticator) {
            return;
        }

        return $this->authenticator->getUser();
    }
}
