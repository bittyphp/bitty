<?php

namespace Bitty\Tests\Stubs;

use Bitty\Http\Response;
use Psr\Http\Message\ResponseInterface;

class InvokableResponseStub
{
    /**
     * Mock invokable.
     *
     * @return ResponseInterface
     */
    public function __invoke()
    {
        return new Response();
    }
}
