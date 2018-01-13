<?php

namespace Bitty\Security\Authentication;

use Bitty\Security\Authentication\AuthenticatorInterface;
use Bitty\Security\Encoder\EncoderInterface;
use Bitty\Security\Exception\AuthenticationException;
use Bitty\Security\User\Provider\UserProviderInterface;
use Bitty\Security\User\UserInterface;

class Authenticator implements AuthenticatorInterface
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
     * @param EncoderInterface[]|EncoderInterface $encoders
     * @param string $sessionKey
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        UserProviderInterface $userProvider,
        $encoders,
        $sessionKey = 'auth.user'
    ) {
        $this->userProvider = $userProvider;
        $this->sessionKey   = $sessionKey;

        if (is_object($encoders)) {
            $this->addEncoder($encoders, UserInterface::class);
        } elseif (is_array($encoders)) {
            foreach ($encoders as $class => $encoder) {
                $this->addEncoder($encoder, $class);
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Encoder must be an instance of %s or an array; %s given.',
                    EncoderInterface::class,
                    gettype($encoders)
                )
            );
        }
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

        // http://php.net/manual/en/features.session.security.management.php#features.session.security.management.session-id-regeneration
        // http://php.net/manual/en/function.session-regenerate-id.php
        $_SESSION['auth.destroyed'] = time();
        session_regenerate_id();
        $_SESSION[$this->sessionKey] = serialize($user);
        unset($_SESSION['auth.destroyed']);

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
     * Adds an encoder for the given user class.
     *
     * @param EncoderInterface $encoder
     * @param string $userClass
     *
     * @throws AuthenticationException
     */
    protected function addEncoder(EncoderInterface $encoder, $userClass)
    {
        if (!class_exists($userClass) && !interface_exists($userClass)) {
            throw new AuthenticationException(
                sprintf('User class %s does not exist.', $userClass)
            );
        }

        $this->encoders[$userClass] = $encoder;
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
