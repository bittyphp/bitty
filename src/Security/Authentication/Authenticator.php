<?php

namespace Bitty\Security\Authentication;

use Bitty\Security\Authentication\AuthenticatorInterface;
use Bitty\Security\Encoder\EncoderCollection;
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
     * @var EncoderCollection
     */
    protected $encoders = null;

    /**
     * @param UserProviderInterface $userProvider
     * @param EncoderInterface[]|EncoderInterface $encoders
     */
    public function __construct(UserProviderInterface $userProvider, $encoders)
    {
        $this->userProvider = $userProvider;
        $this->encoders     = new EncoderCollection($encoders);
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password)
    {
        $user = $this->userProvider->getUser($username);
        if (!$user) {
            throw new AuthenticationException('Invalid username.');
        }

        $encoder = $this->encoders->getEncoder($user);
        $hash    = $user->getPassword();
        $salt    = $user->getSalt();

        if (!$encoder->verify($hash, $password, $salt)) {
            throw new AuthenticationException('Invalid password.');
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function reloadUser(UserInterface $user)
    {
        return $this->userProvider->getUser($user->getUsername());
    }
}
