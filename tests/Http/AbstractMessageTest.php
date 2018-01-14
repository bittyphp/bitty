<?php

namespace Bitty\Tests\Http;

use Bitty\Http\AbstractMessage;
use Bitty\Tests\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class AbstractMessageTest extends TestCase
{
    /**
     * @var AbstractMessage
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = $this->getMockForAbstractClass(AbstractMessage::class);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(MessageInterface::class, $this->fixture);
    }

    public function testWithProtocolVersion()
    {
        $clone = $this->fixture->withProtocolVersion('1.0');

        $new = $clone->getProtocolVersion();
        $old = $this->fixture->getProtocolVersion();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('1.0', $new);
        $this->assertEquals('1.1', $old);
    }

    public function testWithProtocolVersionThrowsException()
    {
        $invalidVersion = uniqid();

        $message = 'Invalid protocol version "'.$invalidVersion.'". '
            .'Valid versions are: ["1.0", "1.1", "2.0", "2"]';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->withProtocolVersion($invalidVersion);
    }

    public function testWithHeaderAddsNewHeader()
    {
        $header = uniqid('header');
        $value  = uniqid('value');

        $clone = $this->fixture->withHeader($header, $value);

        $new = $clone->getHeaders();
        $old = $this->fixture->getHeaders();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([$header => [$value]], $new);
        $this->assertEquals([], $old);
    }

    public function testWithHeaderReplacesExistingHeader()
    {
        $headerA = uniqid('header');
        $headerB = strtoupper($headerA);
        $headerC = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $valueC  = uniqid('value');

        $cloneA = $this->fixture->withHeader($headerA, $valueA)->withHeader($headerC, $valueC);
        $old    = $cloneA->getHeaders();

        $cloneB = $cloneA->withHeader($headerB, $valueB);
        $new    = $cloneB->getHeaders();

        $this->assertNotSame($cloneA, $cloneB);
        $this->assertEquals([$headerC => [$valueC], $headerA => [$valueA]], $old);
        $this->assertEquals([$headerC => [$valueC], $headerB => [$valueB]], $new);
    }

    /**
     * @dataProvider sampleHeaderExceptions
     */
    public function testWithHeaderThrowsException($header, $value, $expected)
    {
        $this->setExpectedException(\InvalidArgumentException::class, $expected);

        $this->fixture->withHeader($header, $value);
    }

    public function testWithAddedHeaderAddsNewHeader()
    {
        $header = uniqid('header');
        $value  = uniqid('value');

        $clone = $this->fixture->withAddedHeader($header, $value);

        $new = $clone->getHeaders();
        $old = $this->fixture->getHeaders();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([$header => [$value]], $new);
        $this->assertEquals([], $old);
    }

    public function testWithAddedHeaderAddsToExistingHeader()
    {
        $headerA = uniqid('header');
        $headerB = strtoupper($headerA);
        $headerC = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $valueC  = uniqid('value');

        $cloneA = $this->fixture->withHeader($headerA, $valueA)->withHeader($headerC, $valueC);
        $old    = $cloneA->getHeaders();

        $cloneB = $cloneA->withAddedHeader($headerB, $valueB);
        $new    = $cloneB->getHeaders();

        $this->assertNotSame($cloneA, $cloneB);
        $this->assertEquals([$headerA => [$valueA], $headerC => [$valueC]], $old);
        $this->assertEquals([$headerA => [$valueA, $valueB], $headerC => [$valueC]], $new);
    }

    /**
     * @dataProvider sampleHeaderExceptions
     */
    public function testWithAddedHeaderThrowsException($header, $value, $expected)
    {
        $this->setExpectedException(\InvalidArgumentException::class, $expected);

        $this->fixture->withAddedHeader($header, $value);
    }

    public function sampleHeaderExceptions()
    {
        $header = uniqid('header');

        return [
            'object value type' => [
                $header,
                new \stdClass(),
                'Values for header "'.$header.'" must be a string or array; object given.',
            ],
            'null value type' => [
                $header,
                null,
                'Values for header "'.$header.'" must be a string or array; NULL given.',
            ],
            'false value type' => [
                $header,
                (bool) rand(0, 1),
                'Values for header "'.$header.'" must be a string or array; boolean given.',
            ],
            'int value type' => [
                $header,
                rand(),
                'Values for header "'.$header.'" must be a string or array; integer given.',
            ],
            'object array value' => [
                $header,
                [uniqid(), new \stdClass(), uniqid()],
                'Values for header "'.$header.'" must contain only strings; object given.',
            ],
            'array array value' => [
                $header,
                [uniqid(), [], uniqid()],
                'Values for header "'.$header.'" must contain only strings; array given.',
            ],
            'null value type' => [
                $header,
                [uniqid(), null, uniqid()],
                'Values for header "'.$header.'" must contain only strings; NULL given.',
            ],
            'false value type' => [
                $header,
                [uniqid(), (bool) rand(0, 1), uniqid()],
                'Values for header "'.$header.'" must contain only strings; boolean given.',
            ],
            'int value type' => [
                $header,
                [uniqid(), rand(), uniqid()],
                'Values for header "'.$header.'" must contain only strings; integer given.',
            ],
        ];
    }

    public function testWithoutHeaderWhenHeaderNotPresent()
    {
        $clone = $this->fixture->withoutHeader(uniqid());

        $new = $clone->getHeaders();
        $old = $this->fixture->getHeaders();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([], $new);
        $this->assertEquals([], $old);
    }

    public function testWithoutHeaderWhenHeaderIsPresent()
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');

        $cloneA = $this->fixture->withHeader($headerA, $valueA)->withHeader($headerB, $valueB);
        $old    = $cloneA->getHeaders();

        $cloneB = $cloneA->withoutHeader(strtoupper($headerA));
        $new    = $cloneB->getHeaders();

        $this->assertNotSame($cloneA, $cloneB);
        $this->assertEquals([$headerA => [$valueA], $headerB => [$valueB]], $old);
        $this->assertEquals([$headerB => [$valueB]], $new);
    }

    public function testHasHeaderWhenNotPresent()
    {
        $actual = $this->fixture->hasHeader(uniqid());

        $this->assertFalse($actual);
    }

    public function testHasHeaderWhenPresent()
    {
        $header = uniqid('header');

        $clone  = $this->fixture->withHeader($header, uniqid());
        $actual = $clone->hasHeader(strtoupper($header));

        $this->assertTrue($actual);
    }

    public function testGetHeaderWhenNotPresent()
    {
        $actual = $this->fixture->getHeader(uniqid());

        $this->assertEquals([], $actual);
    }

    public function testGetHeaderWhenPresent()
    {
        $header = uniqid('header');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        $clone  = $this->fixture->withHeader($header, [$valueA, $valueB]);
        $actual = $clone->getHeader(strtoupper($header));

        $this->assertEquals([$valueA, $valueB], $actual);
    }

    public function testGetHeaderLineWhenNotPresent()
    {
        $actual = $this->fixture->getHeaderLine(uniqid());

        $this->assertEquals('', $actual);
    }

    public function testGetHeaderLineWhenPresent()
    {
        $header = uniqid('header');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        $clone  = $this->fixture->withHeader($header, [$valueA, $valueB]);
        $actual = $clone->getHeaderLine(strtoupper($header));

        $this->assertEquals($valueA.','.$valueB, $actual);
    }

    public function testWithBody()
    {
        $body = $this->createMock(StreamInterface::class);

        $clone = $this->fixture->withBody($body);
        $old   = $this->fixture->getBody();
        $new   = $clone->getBody();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertNull($old);
        $this->assertSame($body, $new);
    }
}
