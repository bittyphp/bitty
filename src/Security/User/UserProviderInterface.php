<?php

namespace Bizurkur\Bitty\Security\User;

use Bizurkur\Bitty\Security\User\UserInterface;

interface UserProviderInterface
{
    /**
     * Gets the user.
     *
     * @return UserInterface|null
     */
    public function getUser($username);
}
