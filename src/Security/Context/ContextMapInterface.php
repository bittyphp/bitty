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
     * If the request is not secured, it should return the user from the default
     * context or from the first available context if no default is set.
     *
     * @param ServerRequestInterface $request
     *
     * @return UserInterface|null
     */
    public function getUser(ServerRequestInterface $request);
}
