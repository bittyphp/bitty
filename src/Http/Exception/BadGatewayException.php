<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpException;

class BadGatewayException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Bad Gateway';

    /**
     * @var int
     */
    protected $code = 502;

    /**
     * @var string
     */
    protected $title = '502 Bad Gateway';

    /**
     * @var string
     */
    protected $description = 'The server received an invalid response from an upstream server.';
}
