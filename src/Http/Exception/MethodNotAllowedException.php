<?php

namespace Bizurkur\Bitty\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpException;

class MethodNotAllowedException extends HttpException
{
    /**
     * @var string
     */
    protected $message = 'Method Not Allowed';

    /**
     * @var int
     */
    protected $code = 405;

    /**
     * @var string
     */
    protected $title = '405 Method Not Allowed';

    /**
     * @var string
     */
    protected $description = 'The method specified is not allowed for the resource identified.';
}
