<?php

namespace Bitty\Security;

use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Security\Context\ContextMap;
use Bitty\Security\Context\ContextMapInterface;
use Bitty\Security\Shield\ShieldInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityMiddleware implements MiddlewareInterface
{
    /**
     * @var ShieldInterface
     */
    protected $shield = null;

    /**
     * @param ShieldInterface $shield
     * @param ContextMapInterface|null $contextMap
     */
    public function __construct(ShieldInterface $shield, ContextMapInterface $contextMap = null)
    {
        if ($contextMap) {
            $contextMap->add($shield->getContext());
        }

        $this->shield = $shield;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $response = $this->shield->handle($request);
        if ($response) {
            return $response;
        }

        return $handler->handle($request);
    }
}
