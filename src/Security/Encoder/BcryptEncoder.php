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
     * @param int $maxPasswordLength
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
        $this->blockLongPasswords();

        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    /**
     * {@inheritDoc}
     */
    public function verify($encoded, $password, $salt = null)
    {
        $this->blockLongPasswords();

        return password_verify($password, $encoded);
    }
}
