<?php

namespace Bizurkur\Bitty\Tests\Http;

use Bizurkur\Bitty\Http\JsonResponse;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class JsonResponseTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new JsonResponse();

        $this->assertInstanceOf(ResponseInterface::class, $fixture);
    }

    public function testHeaders()
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');

        $fixture = new JsonResponse(
            '',
            200,
            [
                $headerA => $valueA,
                'CoNTeNt-TyPe' => uniqid(),
                $headerB => [$valueB],
            ]
        );

        $actual   = $fixture->getHeaders();
        $expected = [
            $headerA => [$valueA],
            'Content-Type' => ['application/json'],
            $headerB => [$valueB],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testBodyIsJsonEncoded()
    {
        $data = [uniqid('a') => uniqid('a'), uniqid('b') => uniqid('b')];
        $json = json_encode($data);

        $fixture = new JsonResponse($data);
        $actual  = $fixture->getBody();

        $this->assertEquals($json, (string) $actual);
    }

    public function testStatusCode()
    {
        $statusCode = rand(1, 5) * 100 + rand(0, 3);

        $fixture = new JsonResponse('', $statusCode);
        $actual  = $fixture->getStatusCode();

        $this->assertEquals($statusCode, $actual);
    }
}
