<?php

namespace Bitty\Security\User\Provider;

use Bitty\Security\User\UserInterface;

interface UserProviderInterface
{
    /**
     * Gets the user.
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    public function getUser($username);
}
