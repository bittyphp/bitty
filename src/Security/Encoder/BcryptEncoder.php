<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Encoder\AbstractEncoder;

class BcryptEncoder extends AbstractEncoder
{
    /**
     * @var int
     */
    protected $cost = null;

    /**
     * @param int $cost
     * @param int $maxPasswordLength Use zero to keep the default.
     */
    public function __construct($cost = 10, $maxPasswordLength = 0)
    {
        parent::__construct($maxPasswordLength);

        $this->cost = $cost;
    }

    /**
     * {@inheritDoc}
     */
    public function encode($password, $salt = null)
    {
        $this->checkPassword($password);

        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    /**
     * {@inheritDoc}
     */
    public function verify($encoded, $password, $salt = null)
    {
        $this->checkPassword($password);

        return password_verify($password, $encoded);
    }
}
