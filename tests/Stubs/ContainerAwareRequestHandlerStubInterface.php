<?php

namespace Bitty\Tests\Stubs;

use Bitty\Container\ContainerAwareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface ContainerAwareRequestHandlerStubInterface extends RequestHandlerInterface, ContainerAwareInterface
{
}
