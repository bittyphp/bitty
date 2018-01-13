<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Encoder\AbstractEncoder;

class PlainTextEncoder extends AbstractEncoder
{
    /**
     * {@inheritDoc}
     */
    public function encode($password, $salt = null)
    {
        return $password;
    }
}
