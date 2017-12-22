<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\Container\ContainerException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
