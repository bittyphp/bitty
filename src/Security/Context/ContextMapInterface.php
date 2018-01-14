<?php

namespace Bitty\Security\Context;

use Bitty\Security\Context\ContextInterface;
use Bitty\Security\User\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ContextMapInterface
{
    /**
     * Adds a context to the map.
     *
     * @param ContextInterface $context
     */
    public function add(ContextInterface $context);

    /**
     * Gets the authenticated user for the request, if any.
     *
     * @param ServerRequestInterface $request
     *
     * @return UserInterface|null
     */
    public function getUser(ServerRequestInterface $request);
}
