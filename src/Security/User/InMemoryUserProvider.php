<?php

namespace Bizurkur\Bitty\Security\User;

use Bizurkur\Bitty\Security\User\User;
use Bizurkur\Bitty\Security\User\UserProviderInterface;

class InMemoryUserProvider implements UserProviderInterface
{
    /**
     * @var string[]
     */
    protected $users = [];

    /**
     * @param string[] $users
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser($username)
    {
        if (!isset($this->users[$username])) {
            return;
        }

        $user = $this->users[$username];
        if (empty($user['password'])) {
            return;
        }

        $password = $user['password'];
        $salt     = empty($user['salt']) ? null : $user['salt'];
        $roles    = empty($user['roles']) ? [] : (array) $user['roles'];

        return new User($username, $password, $salt, $roles);
    }
}
