<?php

namespace Bizurkur\Bitty\Tests\Http;

use Bizurkur\Bitty\Http\RedirectResponse;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class RedirectResponseTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new RedirectResponse(uniqid());

        $this->assertInstanceOf(ResponseInterface::class, $fixture);
    }

    public function testHeaders()
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $uri     = uniqid();

        $fixture = new RedirectResponse(
            $uri,
            200,
            [
                $headerA => $valueA,
                'LoCaTioN' => uniqid(),
                $headerB => [$valueB],
            ]
        );

        $actual   = $fixture->getHeaders();
        $expected = [
            $headerA => [$valueA],
            'Location' => [$uri],
            $headerB => [$valueB],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testStatusCode()
    {
        $statusCode = rand(1, 5) * 100 + rand(0, 3);

        $fixture = new RedirectResponse(uniqid(), $statusCode);
        $actual  = $fixture->getStatusCode();

        $this->assertEquals($statusCode, $actual);
    }
}
