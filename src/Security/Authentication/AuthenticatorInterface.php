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
}
