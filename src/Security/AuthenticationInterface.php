<?php

namespace Bizurkur\Bitty\Security;

use Bizurkur\Bitty\Security\Exception\AuthenticationException;
use Bizurkur\Bitty\Security\User\UserInterface;

interface AuthenticationInterface
{
    /**
     * Authenticates a user.
     *
     * @param string $username
     * @param string $password
     * @param bool $remember
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function authenticate($username, $password, $remember = false);

    /**
     * Deauthenticates a user.
     *
     * @return bool
     */
    public function deauthenticate();

    /**
     * Checks if a user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Gets the authenticated user.
     *
     * @return UserInterface|null
     */
    public function getUser();
}
