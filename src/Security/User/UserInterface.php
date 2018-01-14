<?php

namespace Bitty\Security\User;

interface UserInterface
{
    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Gets the encoded password.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Gets the password salt.
     *
     * @return string|null
     */
    public function getSalt();

    /**
     * Gets the roles the user has.
     *
     * @return string[]
     */
    public function getRoles();
}
