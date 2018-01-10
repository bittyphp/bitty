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
     * @var string
     */
    protected $password = null;

    /**
     * @var string
     */
    protected $salt = null;

    /**
     * @var string[]
     */
    protected $roles = null;

    /**
     * @param string $username
     * @param string $password
     * @param string|null $salt
     * @param string[] $roles
     */
    public function __construct($username, $password, $salt = null, array $roles = [])
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt     = $salt;
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
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
