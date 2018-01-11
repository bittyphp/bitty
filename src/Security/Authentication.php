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
     * @var EncoderInterface[]
     */
    protected $encoders = null;

    /**
     * @var string
     */
    protected $sessionKey = null;

    /**
     * @param UserProviderInterface $userProvider
     * @param EncoderInterface[] $encoders
     * @param string $sessionKey
     */
    public function __construct(
        UserProviderInterface $userProvider,
        array $encoders,
        $sessionKey = 'auth.user'
    ) {
        if ('' === session_id()) {
            session_start();
        }

        $this->userProvider = $userProvider;
        $this->encoders     = $encoders;
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

        $encoder = $this->getEncoder($user);
        $hash    = $user->getPassword();
        $salt    = $user->getSalt();

        if (!$encoder->verify($hash, $password, $salt)) {
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

    /**
     * Gets the password encoder for the given user.
     *
     * @param UserInterface $user
     *
     * @return EncoderInterface
     *
     * @throws AuthenticationException
     */
    protected function getEncoder(UserInterface $user)
    {
        foreach ($this->encoders as $class => $encoder) {
            if ($user instanceof $class) {
                return $encoder;
            }
        }

        throw new AuthenticationException(
            sprintf('Unable to determine encoder for %s.', get_class($user))
        );
    }
}
