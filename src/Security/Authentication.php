<?php

namespace Bizurkur\Bitty\Security;

use Bizurkur\Bitty\Security\AuthenticationInterface;
use Bizurkur\Bitty\Security\Encoder\EncoderInterface;
use Bizurkur\Bitty\Security\Exception\AuthenticationException;
use Bizurkur\Bitty\Security\User\Provider\UserProviderInterface;
use Bizurkur\Bitty\Security\User\UserInterface;

class Authentication implements AuthenticationInterface
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider = null;

    /**
     * @var EncoderInterface
     */
    protected $encoder = null;

    /**
     * @var string
     */
    protected $sessionKey = null;

    /**
     * @param UserProviderInterface $userProvider
     * @param EncoderInterface $encoder
     * @param string $sessionKey
     */
    public function __construct(
        UserProviderInterface $userProvider,
        EncoderInterface $encoder,
        $sessionKey = 'auth.user'
    ) {
        if ('' === session_id()) {
            session_start();
        }

        $this->userProvider = $userProvider;
        $this->encoder      = $encoder;
        $this->sessionKey   = $sessionKey;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password, $remember = false)
    {
        $user = $this->userProvider->getUser($username);
        if (!$user) {
            throw new AuthenticationException('Invalid username.');
        }

        $encoded = $user->getPassword();
        $salt    = $user->getSalt();

        if (!$this->encoder->validate($encoded, $password, $salt)) {
            throw new AuthenticationException('Invalid password.');
        }

        // TODO: Remember me.
        $_SESSION[$this->sessionKey] = serialize($user);

        return true;
    }

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
        // TODO: Base this on TTL or expire time.

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
}
