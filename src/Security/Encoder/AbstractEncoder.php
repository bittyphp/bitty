<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Encoder\EncoderInterface;
use Bitty\Security\Exception\AuthenticationException;

abstract class AbstractEncoder implements EncoderInterface
{
    /**
     * {@inheritDoc}
     */
    abstract public function encode($password, $salt = null);

    /**
     * {@inheritDoc}
     */
    public function verify($encoded, $password, $salt = null)
    {
        $this->checkPassword($password);

        return hash_equals($encoded, $this->encode($password, $salt));
    }

    /**
     * Checks for passwords that are too long.
     *
     * @see https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
     *
     * @param string $password
     *
     * @throws AuthenticationException
     */
    protected function checkPassword($password)
    {
        if (strlen($password) > EncoderInterface::MAX_PASSWORD_LEN) {
            throw new AuthenticationException('Invalid password.');
        }
    }
}
