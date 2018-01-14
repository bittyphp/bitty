<?php

namespace Bitty\Security\Authorization;

use Bitty\Security\Exception\AuthorizationException;
use Bitty\Security\User\UserInterface;

interface AuthorizerInterface
{
    /**
     * Authorizes a user.
     *
     * @param UserInterface $user
     * @param string[] $roles
     *
     * @return bool
     *
     * @throws AuthorizationException
     */
    public function authorize(UserInterface $user, array $roles);
}
