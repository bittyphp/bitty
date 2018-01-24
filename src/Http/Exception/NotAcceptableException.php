<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpException;

class NotAcceptableException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Not Acceptable';

    /**
     * @var int
     */
    protected $code = 406;

    /**
     * @var string
     */
    protected $title = '406 Not Acceptable';

    /**
     * @var string
     */
    protected $description = 'The resource is not capable of generating responses acceptable to the requested accept headers.';
}
