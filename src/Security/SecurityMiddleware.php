<?php

namespace Bitty\Security;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerInterface;
use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Security\Context\ContextMapInterface;
use Bitty\Security\Context\ContextMapServiceProvider;
use Bitty\Security\Shield\ShieldInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

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
    public function setContainer(PsrContainerInterface $container)
    {
        if ($container instanceof ContainerInterface) {
            $container->register([new ContextMapServiceProvider()]);

            $contextMap = $container->get('security_context');
            $contextMap->add($this->shield->getContext());
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
