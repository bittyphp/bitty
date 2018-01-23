<?php

namespace Bitty\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouteMatcherInterface
{
    /**
     * Matches a route to the given request.
     *
     * @param ServerRequestInterface $request
     *
     * @return RouteInterface
     *
     * @throws NotFoundException When no match found.
     */
    public function match(ServerRequestInterface $request);
}
