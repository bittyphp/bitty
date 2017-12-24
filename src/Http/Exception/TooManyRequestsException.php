<?php

namespace Bizurkur\Bitty\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpException;

class TooManyRequestsException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Too Many Requests';

    /**
     * @var int
     */
    protected $code = 429;

    /**
     * @var string
     */
    protected $title = '429 Too Many Requests';

    /**
     * @var string
     */
    protected $description = 'Too many requests sent in a given amount of time.';
}
