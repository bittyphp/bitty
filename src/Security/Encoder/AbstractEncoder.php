<?php

namespace Bizurkur\Bitty\Security\Encoder;

use Bizurkur\Bitty\Security\Encoder\EncoderInterface;
use Bizurkur\Bitty\Security\Exception\AuthenticationException;

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
    public function validate($encoded, $password, $salt = null)
    {
        if ($this->isPasswordTooLong($password)) {
            throw new AuthenticationException(
                sprintf(
                    'Password is too long. Max of %d characters allowed, %d given.',
                    $this->maxPasswordLength,
                    strlen($password)
                )
            );
        }

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
}
