<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpException;

class ForbiddenException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Forbidden';

    /**
     * @var int
     */
    protected $code = 403;

    /**
     * @var string
     */
    protected $title = '403 Forbidden';

    /**
     * @var string
     */
    protected $description = 'The server understood the request, but is refusing to fulfill it.';
}
