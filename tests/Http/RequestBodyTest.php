<?php

namespace Bitty\Tests\Http;

use Bitty\Http\RequestBody;
use Bitty\Tests\TestCase;
use Psr\Http\Message\StreamInterface;

class RequestBodyTest extends TestCase
{
    /**
     * @var RequestBody
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new RequestBody();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(StreamInterface::class, $this->fixture);
    }
}
