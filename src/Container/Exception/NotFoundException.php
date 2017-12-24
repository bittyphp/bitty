<?php

namespace Bizurkur\Bitty\Container\Exception;

use Bizurkur\Bitty\Container\Exception\ContainerException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
