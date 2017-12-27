<?php

namespace Bizurkur\Bitty\Router;

use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouteHandlerInterface
{
    /**
     * Handles the request.
     *
     * @param ServerRequestInterface $request Request to handle.
     *
     * @return mixed
     *
     * @throws HttpExceptionInterface
     */
    public function handle(ServerRequestInterface $request);
}
