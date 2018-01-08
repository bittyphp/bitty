<?php

namespace Bizurkur\Bitty\Security\Authentication;

use Bizurkur\Bitty\Security\Authentication\ProviderInterface;
use Bizurkur\Bitty\Security\User\UserInterface;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    protected $sessionKey = null;

    /**
     * @param string $sessionKey
     */
    public function __construct($sessionKey = 'auth.user')
    {
        if ('' === session_id()) {
            session_start();
        }

        $this->sessionKey = $sessionKey;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function authenticate($username, $password, $remember = false);

    /**
     * {@inheritDoc}
     */
    abstract public function encodePassword($password, $salt = null);

    /**
     * {@inheritDoc}
     */
    public function deauthenticate()
    {
        if (isset($_SESSION[$this->sessionKey])) {
            unset($_SESSION[$this->sessionKey]);
        }

        return !$this->isAuthenticated();
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthenticated()
    {
        $user = $this->getUser();

        return !empty($user);
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {
        if (!empty($_SESSION[$this->sessionKey])) {
            return unserialize($_SESSION[$this->sessionKey]);
        }

        return null;
    }

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     */
    protected function setUser(UserInterface $user)
    {
        $_SESSION[$this->sessionKey] = serialize($user);
    }
}
