<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpException;

class InternalServerErrorException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Internal Server Error';

    /**
     * @var int
     */
    protected $code = 500;

    /**
     * @var string
     */
    protected $title = '500 Internal Server Error';

    /**
     * @var string
     */
    protected $description = 'The server encountered an unexpected condition '
        .'which prevented it from fulfilling the request.';
}
