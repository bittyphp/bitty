<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Encoder\EncoderInterface;
use Bitty\Security\Exception\AuthenticationException;

abstract class AbstractEncoder implements EncoderInterface
{
    /**
     * @var int
     */
    protected $maxPasswordLength = EncoderInterface::MAX_PASSWORD_LEN;

    /**
     * @param int $maxPasswordLength Use zero to keep the default.
     */
    public function __construct($maxPasswordLength = 0)
    {
        if ($maxPasswordLength > 0) {
            $this->maxPasswordLength = $maxPasswordLength;
        }
    }

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

        return $this->encode($password, $salt) === $encoded;
    }

    /**
     * Checks if the password is too long.
     *
     * @see https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
     *
     * @param string $password Unencoded password.
     *
     * @return bool
     */
    public function isPasswordTooLong($password)
    {
        return strlen($password) > $this->maxPasswordLength;
    }

    /**
     * Checks for passwords that are too long.
     *
     * @param string $password
     *
     * @throws AuthenticationException
     */
    protected function checkPassword($password)
    {
        if (!$this->isPasswordTooLong($password)) {
            return;
        }

        throw new AuthenticationException(
            sprintf(
                'Password is too long. Max of %d characters allowed, %d given.',
                $this->maxPasswordLength,
                strlen($password)
            )
        );
    }
}
