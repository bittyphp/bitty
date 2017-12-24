<?php

namespace Bizurkur\Bitty\Router;

use Bizurkur\Bitty\Http\Exception\HttpException;
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
     * @throws HttpException
     */
    public function handle(ServerRequestInterface $request);
}
