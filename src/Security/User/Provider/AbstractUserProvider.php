<?php

namespace Bitty\Security\User\Provider;

use Bitty\Security\Exception\AuthenticationException;
use Bitty\Security\User\Provider\UserProviderInterface;

abstract class AbstractUserProvider implements UserProviderInterface
{
    /**
     * @var int
     */
    protected $maxUsernameLength = UserProviderInterface::MAX_USERNAME_LEN;

    /**
     * @param int $maxUsernameLength Use zero to keep the default.
     */
    public function __construct($maxUsernameLength = 0)
    {
        if ($maxUsernameLength > 0) {
            $this->maxUsernameLength = $maxUsernameLength;
        }
    }

    /**
     * {@inheritDoc}
     */
    abstract public function getUser($username);

    /**
     * Checks if the username is too long.
     *
     * @param string $username
     *
     * @return bool
     */
    public function isUsernameTooLong($username)
    {
        return strlen($username) > $this->maxUsernameLength;
    }

    /**
     * Checks for usernames that are too long.
     *
     * @param string $username
     *
     * @throws AuthenticationException
     */
    protected function checkUsername($username)
    {
        if (!$this->isUsernameTooLong($username)) {
            return;
        }

        throw new AuthenticationException('Invalid username.');
    }
}
