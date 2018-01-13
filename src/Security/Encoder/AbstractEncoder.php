<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Encoder\EncoderInterface;
use Bitty\Security\Exception\AuthenticationException;

abstract class AbstractEncoder implements EncoderInterface
{
    /**
     * @var int
     */
    protected $maxPasswordLength = null;

    /**
     * @param int $maxPasswordLength
     */
    public function __construct($maxPasswordLength = 4096)
    {
        $this->maxPasswordLength = $maxPasswordLength;
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
        $this->blockLongPasswords();

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

    protected function blockLongPasswords($password)
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
