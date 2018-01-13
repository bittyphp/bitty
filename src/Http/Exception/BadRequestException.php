<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpException;

class BadRequestException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Bad Request';

    /**
     * @var int
     */
    protected $code = 400;

    /**
     * @var string
     */
    protected $title = '400 Bad Request';

    /**
     * @var string
     */
    protected $description = 'The request could not be understood by the server due to malformed syntax.';
}
