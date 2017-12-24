<?php

namespace Bizurkur\Bitty\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Unauthorized';

    /**
     * @var int
     */
    protected $code = 401;

    /**
     * @var string
     */
    protected $title = '401 Unauthorized';

    /**
     * @var string
     */
    protected $description = 'The request requires user authentication.';
}
