<?php

namespace Bitty\Security\User\Provider;

use Bitty\Security\User\UserInterface;

interface UserProviderInterface
{
    /**
     * Gets the user.
     *
     * @return UserInterface|null
     */
    public function getUser($username);
}
