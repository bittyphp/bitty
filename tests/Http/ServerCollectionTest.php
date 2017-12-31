<?php

namespace Bizurkur\Bitty\Tests\Http;

use Bizurkur\Bitty\CollectionInterface;
use Bizurkur\Bitty\Http\ServerCollection;
use Bizurkur\Bitty\Tests\TestCase;

class ServerCollectionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new ServerCollection();

        $this->assertInstanceOf(CollectionInterface::class, $fixture);
    }

    public function testGetHeaders()
    {
        $server   = [
            'HTTP_HOST' => uniqid(),
            'HTTP_FOO_BAR' => uniqid(),
            'CONTENT_TYPE' => uniqid(),
            'CONTENT_MD5' => uniqid(),
            'CONTENT_LENGTH' => rand(),
        ];
        $expected = [
            'Host' => [$server['HTTP_HOST']],
            'Foo-Bar' => [$server['HTTP_FOO_BAR']],
            'Content-Type' => [$server['CONTENT_TYPE']],
            'Content-MD5' => [$server['CONTENT_MD5']],
            'Content-Length' => [$server['CONTENT_LENGTH']],
        ];

        $fixture = new ServerCollection($server);
        $actual  = $fixture->getHeaders();

        $this->assertEquals($expected, $actual);
    }
}
