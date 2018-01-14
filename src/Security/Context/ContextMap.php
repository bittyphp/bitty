<?php

namespace Bitty\Security\Context;

use Bitty\Security\Context\ContextInterface;
use Bitty\Security\Context\ContextMapInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContextMap implements ContextMapInterface
{
    /**
     * @var ContextInterface[]
     */
    protected $contexts = [];

    /**
     * {@inheritDoc}
     */
    public function add(ContextInterface $context)
    {
        $this->contexts[] = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser(ServerRequestInterface $request)
    {
        foreach ($this->contexts as $context) {
            if ($context->isShielded($request)) {
                return $context->get('user');
            }
        }
    }
}
