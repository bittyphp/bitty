<?php

namespace Bizurkur\Bitty\Security\User;

interface UserInterface
{
    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Gets the roles the user has.
     *
     * @return string[]
     */
    public function getRoles();
}
