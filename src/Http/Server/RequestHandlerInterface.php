<?php

namespace Bitty\Http\Server;

use Bitty\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * An HTTP request handler processes an HTTP request and produces an HTTP response.
 * This interface defines the methods required to use the request handler.
 *
 * NOTE: This is a placeholder until PSR-15 is approved. The only difference
 * right now is that it has a documented thrown exception.
 */
interface RequestHandlerInterface
{
    /**
     * Handles the request and returns a response.
     *
     * @param ServerRequestInterface $request Request to handle.
     *
     * @return ResponseInterface
     *
     * @throws HttpExceptionInterface
     */
    public function handle(ServerRequestInterface $request);
}
