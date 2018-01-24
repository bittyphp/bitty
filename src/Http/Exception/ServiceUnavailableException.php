<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpException;

class ServiceUnavailableException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Service Unavailable';

    /**
     * @var int
     */
    protected $code = 503;

    /**
     * @var string
     */
    protected $title = '503 Service Unavailable';

    /**
     * @var string
     */
    protected $description = 'The server is currently unable to handle the request due to a temporary overloading or maintenance of the server.';
}
