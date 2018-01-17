<?php

namespace Bitty\Security\Authentication;

use Bitty\Security\Exception\AuthenticationException;
use Bitty\Security\User\UserInterface;

interface AuthenticatorInterface
{
    /**
     * Authenticates a user.
     *
     * @param string $username
     * @param string $password
     *
     * @return UserInterface
     *
     * @throws AuthenticationException
     */
    public function authenticate($username, $password);

    /**
     * Reloads a user.
     *
     * This ensures the user is still valid and loads any permission changes
     * that might have occurred.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function reloadUser(UserInterface $user);
}
