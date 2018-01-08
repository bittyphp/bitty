<?php

namespace Bizurkur\Bitty\Security\User;

use Bizurkur\Bitty\Security\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var string[]
     */
    protected $roles = null;

    /**
     * @param string $username
     * @param string[] $roles
     */
    public function __construct($username, array $roles = [])
    {
        $this->username = $username;
        $this->roles    = $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
