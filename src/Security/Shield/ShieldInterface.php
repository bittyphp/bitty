<?php

namespace Bitty\Security\Shield;

use Bitty\Security\Context\ContextInterface;
use Bitty\Security\Exception\SecurityException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ShieldInterface
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
     *
     * @throws SecurityException
     */
    public function handle(ServerRequestInterface $request);

    /**
     * Gets the shield context.
     *
     * @return ContextInterface
     */
    public function getContext();
}
