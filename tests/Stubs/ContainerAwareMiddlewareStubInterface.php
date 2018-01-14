<?php

namespace Bitty\Tests\Stubs;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Http\Server\MiddlewareInterface;

interface ContainerAwareMiddlewareStubInterface extends MiddlewareInterface, ContainerAwareInterface
{
}
