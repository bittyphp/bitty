<?php

namespace Bitty\Security\Encoder;

use Bitty\Security\Encoder\EncoderInterface;
use Bitty\Security\Exception\SecurityException;
use Bitty\Security\User\UserInterface;

class EncoderCollection
{
    /**
     * @var EncoderInterface[]
     */
    protected $encoders = [];

    /**
     * @param EncoderInterface[]|EncoderInterface $encoders
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($encoders)
    {
        if (is_object($encoders)) {
            $this->addEncoder($encoders, UserInterface::class);
        } elseif (is_array($encoders)) {
            foreach ($encoders as $class => $encoder) {
                $this->addEncoder($encoder, $class);
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Encoder must be an instance of %s or an array; %s given.',
                    EncoderInterface::class,
                    gettype($encoders)
                )
            );
        }
    }

    /**
     * Adds an encoder for the given user class.
     *
     * @param EncoderInterface $encoder
     * @param string $userClass
     *
     * @throws SecurityException
     */
    public function addEncoder(EncoderInterface $encoder, $userClass)
    {
        if (!class_exists($userClass) && !interface_exists($userClass)) {
            throw new SecurityException(
                sprintf('User class %s does not exist.', $userClass)
            );
        }

        $this->encoders[$userClass] = $encoder;
    }

    /**
     * Gets the password encoder for the given user.
     *
     * @param UserInterface $user
     *
     * @return EncoderInterface
     *
     * @throws SecurityException
     */
    public function getEncoder(UserInterface $user)
    {
        foreach ($this->encoders as $class => $encoder) {
            if ($user instanceof $class) {
                return $encoder;
            }
        }

        throw new SecurityException(
            sprintf('Unable to determine encoder for %s.', get_class($user))
        );
    }
}
