<?php

namespace Bitty\Security\Shield;

use Bitty\Security\Context\ContextCollection;
use Bitty\Security\Context\ContextInterface;
use Bitty\Security\Shield\ShieldInterface;
use Psr\Http\Message\ServerRequestInterface;

class ShieldCollection implements ShieldInterface
{
    /**
     * @var ShieldInterface[]
     */
    protected $shields = null;

    /**
     * @var ContextInterface
     */
    protected $context = null;

    /**
     * @param ShieldInterface[] $shields
     */
    public function __construct(array $shields = [])
    {
        $this->context = new ContextCollection();

        foreach ($shields as $shield) {
            $this->add($shield);
        }
    }

    /**
     * Adds a shield to the collection.
     *
     * @param ShieldInterface $shield
     */
    public function add(ShieldInterface $shield)
    {
        $this->shields[] = $shield;
        $this->context->add($shield->getContext());
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        foreach ($this->shields as $shield) {
            $response = $shield->handle($request);
            if ($response) {
                return $response;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getContext()
    {
        $collection = new ContextCollection();

        foreach ($this->shields as $shield) {
            $collection->add($shield->getContext());
        }

        return $collection;
    }
}
