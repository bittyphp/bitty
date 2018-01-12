<?php

namespace Bizurkur\Bitty\Security\Authentication\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HandlerInterface
{
    /**
     * Handles a request to see if it needs authentication.
     *
     * If authentication is needed, it should return a ResponseInterface.
     * Otherwise it should return null.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface|null
     */
    public function handle(ServerRequestInterface $request);
}
