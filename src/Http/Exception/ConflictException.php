<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpException;

class ConflictException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Conflict';

    /**
     * @var int
     */
    protected $code = 409;

    /**
     * @var string
     */
    protected $title = '409 Conflict';

    /**
     * @var string
     */
    protected $description = 'The request could not be completed due to a '
        .'conflict with the current state of the resource.';
}
