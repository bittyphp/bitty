<?php

namespace Bitty\Security\User\Provider;

use Bitty\Security\User\UserInterface;

interface UserProviderInterface
{
    /**
     * @var int
     */
    const MAX_USERNAME_LEN = 4096;

    /**
     * Gets the user.
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    public function getUser($username);
}
