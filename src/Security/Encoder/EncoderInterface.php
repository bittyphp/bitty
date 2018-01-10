<?php

namespace Bizurkur\Bitty\Security\Encoder;

use Bizurkur\Bitty\Security\Exception\AuthenticationException;

interface EncoderInterface
{
    /**
     * Encodes a password.
     *
     * @param string $password Unencoded password.
     * @param string|null $salt Salt used to encode the password.
     *
     * @return string
     */
    public function encode($password, $salt = null);

    /**
     * Validates an encoded password against the given password.
     *
     * @param string $encoded Encoded password.
     * @param string $password Unencoded password.
     * @param string|null $salt Salt used to encode the password.
     *
     * @return bool
     *
     * @throws AuthenticationException If password is too long.
     */
    public function validate($encoded, $password, $salt = null);
}
