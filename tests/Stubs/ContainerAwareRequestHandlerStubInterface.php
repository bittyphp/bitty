<?php

namespace Bitty\Tests\Stubs;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Middleware\RequestHandlerInterface;

interface ContainerAwareRequestHandlerStubInterface extends RequestHandlerInterface, ContainerAwareInterface
{
}
