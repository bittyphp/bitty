<?php

namespace Bitty\Security;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerInterface;
use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Security\Context\ContextMapInterface;
use Bitty\Security\Shield\ShieldInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    /**
     * @var PsrContainerInterface
     */
    protected $container = null;

    /**
     * @var ContextMapInterface
     */
    protected $contextMap = null;

    /**
     * @var ShieldInterface
     */
    protected $shield = null;

    /**
     * @param ContextMapInterface $contextMap
     * @param ShieldInterface $shield
     */
    public function __construct(ContextMapInterface $contextMap, ShieldInterface $shield)
    {
        $contextMap->add($shield->getContext());

        $this->contextMap = $contextMap;
        $this->shield     = $shield;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(PsrContainerInterface $container)
    {
        if ($container instanceof ContainerInterface
            && !$container->has('security_context')
        ) {
            $container->set('security_context', $this->contextMap);
        }

        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getContainer()
    {
        return $this->container;
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
