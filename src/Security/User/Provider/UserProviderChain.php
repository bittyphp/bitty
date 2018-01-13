<?php

namespace Bizurkur\Bitty\Security\User\Provider;

use Bizurkur\Bitty\Security\User\Provider\UserProviderInterface;

class UserProviderChain implements UserProviderInterface
{
    /**
     * @var UserProviderInterface[]
     */
    protected $providers = [];

    /**
     * @param UserProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            $this->add($provider);
        }
    }

    /**
     * Adds a user provider to the chain.
     *
     * @param UserProviderInterface $userProvider
     */
    public function add(UserProviderInterface $userProvider)
    {
        $this->providers[] = $userProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser($username)
    {
        foreach ($this->providers as $provider) {
            $user = $provider->getUser($username);
            if ($user) {
                return $user;
            }
        }
    }
}