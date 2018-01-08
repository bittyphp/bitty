<?php

namespace Bizurkur\Bitty\Security\Authentication;

use Bizurkur\Bitty\Security\User\UserInterface;

interface ProviderInterface
{
    /**
     * Authenticates a user.
     *
     * @param string $username
     * @param string $password
     * @param bool $remember
     *
     * @return bool
     */
    public function authenticate($username, $password, $remember = false);

    /**
     * Encodes a raw password for authentication.
     *
     * @param string $password The raw, unencoded password.
     * @param string|null $salt The salt to encode the password with.
     *
     * @return string
     */
    public function encodePassword($password, $salt = null);

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
