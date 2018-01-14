<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Encoder\AbstractEncoder;

class MessageDigestEncoder extends AbstractEncoder
{
    /**
     * @var string
     */
    protected $algorithm = null;

    /**
     * @param string $algorithm
     * @param int $maxPasswordLength Use zero to keep the default.
     */
    public function __construct($algorithm, $maxPasswordLength = 0)
    {
        parent::__construct($maxPasswordLength);

        if (!in_array($algorithm, hash_algos())) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a valid hash algorithm.', $algorithm)
            );
        }

        $this->algorithm = $algorithm;
    }

    /**
     * {@inheritDoc}
     */
    public function encode($password, $salt = null)
    {
        $this->checkPassword($password);

        if ($salt) {
            $password = $salt.':'.$password;
        }

        return hash($this->algorithm, $password);
    }
}
