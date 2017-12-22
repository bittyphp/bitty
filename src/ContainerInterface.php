<?php

namespace Bizurkur\Bitty;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Gets a single parameter value.
     *
     * @param string $id ID of parameter.
     *
     * @return mixed
     *
     * @throws NotFoundExceptionInterface
     */
    public function getParameter($id);
}
