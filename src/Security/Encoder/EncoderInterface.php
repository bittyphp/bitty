<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Exception\AuthenticationException;

interface EncoderInterface
{
    /**
     * @var int
     */
    const MAX_PASSWORD_LEN = 4096;

    /**
     * Encodes a password.
     *
     * @param string $password Unencoded password.
     * @param string|null $salt Salt used to encode the password.
     *
     * @return string
     *
     * @throws AuthenticationException If password is too long.
     */
    public function encode($password, $salt = null);

    /**
     * Verifies the given password against the encoded password.
     *
     * @param string $encoded Encoded password.
     * @param string $password Unencoded password.
     * @param string|null $salt Salt used to encode the password.
     *
     * @return bool
     *
     * @throws AuthenticationException If password is too long.
     */
    public function verify($encoded, $password, $salt = null);
}
