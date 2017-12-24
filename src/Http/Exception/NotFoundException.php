<?php

namespace Bizurkur\Bitty\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpException;

class NotFoundException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Not Found';

    /**
     * @var int
     */
    protected $code = 404;

    /**
     * @var string
     */
    protected $title = '404 Not Found';

    /**
     * @var string
     */
    protected $description = 'The server cannot find the requested resource.';
}
