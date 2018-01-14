<?php

namespace Bitty\Router\Exception;

use Bitty\Router\Exception\RouterException;

class NotFoundException extends RouterException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = 'Route not found', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
