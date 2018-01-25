<?php

namespace Bitty\Tests\Stubs;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Http\Server\RequestHandlerInterface;

interface ContainerAwareRequestHandlerStubInterface extends RequestHandlerInterface, ContainerAwareInterface
{
}
