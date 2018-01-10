<?php

namespace Bizurkur\Bitty\Security\Encoder;

use Bizurkur\Bitty\Security\Encoder\AbstractEncoder;

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
