<?php

namespace Bizurkur\Bitty\Http\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpExceptionInterface
{
    /**
     * Gets the request object.
     *
     * @return ServerRequestInterface
     */
    public function getRequest();

    /**
     * Gets the response object.
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Gets the exception title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Gets the exception description.
     *
     * @return string
     */
    public function getDescription();
}
