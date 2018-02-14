<?php

namespace Bitty\Tests\Stubs;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Middleware\MiddlewareInterface;

interface ContainerAwareMiddlewareStubInterface extends MiddlewareInterface, ContainerAwareInterface
{
}
