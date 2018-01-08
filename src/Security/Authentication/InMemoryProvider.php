<?php

namespace Bizurkur\Bitty\Security\Authentication;

use Bizurkur\Bitty\Security\Authentication\AbstractProvider;
use Bizurkur\Bitty\Security\User\User;

class InMemoryProvider extends AbstractProvider
{
    /**
     * @var string[]
     */
    protected $users = [];

    /**
     * @param string[] $users
     * @param string $sessionKey
     */
    public function __construct(array $users, $sessionKey = 'auth.user')
    {
        parent::__construct($sessionKey);
        $this->users = $users;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password, $remember = false)
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        $user = $this->users[$username];
        if (empty($user['password'])) {
            return false;
        }

        $encodedPassword = $this->encodePassword($password);
        if ($encodedPassword !== $user['password']) {
            return false;
        }

        $roles = empty($user['roles']) ? [] : (array) $user['roles'];

        $this->setUser(new User($username, $roles));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function encodePassword($password, $salt = null)
    {
        return $password;
    }
}
