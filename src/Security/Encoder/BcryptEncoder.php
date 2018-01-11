<?php

namespace Bizurkur\Bitty\Security\Encoder;

use Bizurkur\Bitty\Security\Encoder\AbstractEncoder;
use Bizurkur\Bitty\Security\Exception\AuthenticationException;

class BcryptEncoder extends AbstractEncoder
{
    /**
     * @var int
     */
    protected $cost = null;

    /**
     * @param int $cost
     */
    public function __construct($cost = 10, $maxPasswordLength = 4096)
    {
        parent::__construct($maxPasswordLength);

        $this->cost = $cost;
    }

    /**
     * {@inheritDoc}
     */
    public function encode($password, $salt = null)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

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

        return password_verify($password, $encoded);
    }
}
