<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Stream;
use Bitty\Tests\TestCase;
use Psr\Http\Message\StreamInterface;

class StreamTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new Stream('');

        $this->assertInstanceOf(StreamInterface::class, $fixture);
    }

    public function testExceptionThrown()
    {
        $message = Stream::class.' must be constructed with a resource or string; integer given.';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        new Stream(rand());
    }

    public function testToString()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $this->assertEquals($content, (string) $fixture);
    }

    public function testToStringWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $this->assertEquals('', (string) $fixture);
    }

    public function testCloseWhenNotAttached()
    {
        $fixture = new Stream(uniqid());

        $fixture->detach();
        $actual = $fixture->close();

        $this->assertNull($actual);
    }

    public function testDetach()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $stream = $fixture->detach();
        $actual = stream_get_contents($stream, -1, 0);

        $this->assertEquals('', (string) $fixture);
        $this->assertEquals($content, $actual);
    }

    public function testGetSize()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $actual = $fixture->getSize();

        $this->assertEquals(strlen($content), $actual);
    }

    public function testGetSizeWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $actual = $fixture->getSize();

        $this->assertNull($actual);
    }

    public function testTell()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $this->assertEquals(0, $fixture->tell());
        $fixture->seek(2);
        $this->assertEquals(2, $fixture->tell());
    }

    public function testTellWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $message = 'Unable to get position of stream.';
        $this->setExpectedException(\RuntimeException::class, $message);

        $fixture->tell();
    }

    public function testEof()
    {
        $fixture = new Stream(uniqid());

        $this->assertFalse($fixture->eof());
        $fixture->seek(0, SEEK_END);
        $fixture->read(1);
        $this->assertTrue($fixture->eof());
    }

    public function testEofWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $this->assertTrue($fixture->eof());
    }

    public function testIsSeekable()
    {
        $fixture = new Stream(uniqid());

        $this->assertTrue($fixture->isSeekable());
    }

    public function testIsSeekableWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $this->assertFalse($fixture->isSeekable());
    }

    public function testSeek()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $seek   = rand(0, strlen($content));
        $actual = $fixture->seek($seek, SEEK_SET);

        $this->assertNull($actual);
    }

    public function testSeekThrowsException()
    {
        $fixture = new Stream(uniqid());

        $message = 'Failed to seek to offset 1.';
        $this->setExpectedException(\RuntimeException::class, $message);

        $fixture->seek(1, SEEK_END);
    }

    public function testSeekWhenNotSeekable()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $message = 'Stream is not seekable.';
        $this->setExpectedException(\RuntimeException::class, $message);

        $fixture->seek(rand());
    }

    public function testRewind()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $fixture->seek(0, SEEK_END);
        $this->assertEquals(strlen($content), $fixture->tell());

        $fixture->rewind();
        $this->assertEquals(0, $fixture->tell());
    }

    public function testRewindWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $message = 'Failed to rewind stream.';
        $this->setExpectedException(\RuntimeException::class, $message);

        $fixture->rewind();
    }

    public function testIsWritable()
    {
        $fixture = new Stream(uniqid());

        $this->assertTrue($fixture->isWritable());
    }

    public function testIsWritableWhenReadOnly()
    {
        $stream  = fopen('php://temp', 'r');
        $fixture = new Stream($stream);

        $this->assertFalse($fixture->isWritable());
    }

    public function testIsWritableWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $this->assertFalse($fixture->isWritable());
    }

    public function testWrite()
    {
        $content = uniqid('content');
        $fixture = new Stream('');

        $fixture->write($content);

        $this->assertEquals($content, (string) $fixture);
    }

    public function testWriteWhenNotAttached()
    {
        $fixture = new Stream('');
        $fixture->close();

        $message = 'Failed to write to stream.';
        $this->setExpectedException(\RuntimeException::class, $message);

        $fixture->write(uniqid());
    }

    public function testIsReadable()
    {
        $fixture = new Stream(uniqid());

        $this->assertTrue($fixture->isReadable());
    }

    public function testIsReadableWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $this->assertFalse($fixture->isReadable());
    }

    public function testRead()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $actual = $fixture->read(strlen($content));

        $this->assertEquals($content, $actual);
    }

    public function testReadWhenNotAttached()
    {
        $fixture = new Stream('');
        $fixture->close();

        $message = 'Failed to read from stream.';
        $this->setExpectedException(\RuntimeException::class, $message);

        $fixture->read(rand());
    }

    public function testGetContents()
    {
        $content = uniqid('content');
        $fixture = new Stream($content);

        $actual = $fixture->getContents();

        $this->assertEquals($content, $actual);
    }

    public function testGetContentsWhenNotAttached()
    {
        $fixture = new Stream('');
        $fixture->close();

        $message = 'Failed to get contents of stream.';
        $this->setExpectedException(\RuntimeException::class, $message);

        $fixture->getContents();
    }

    public function testGetMetadata()
    {
        $fixture = new Stream(uniqid());

        $actual = $fixture->getMetadata();

        $expected = [
            'wrapper_type' => 'PHP',
            'stream_type' => 'TEMP',
            'mode' => 'w+b',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => 'php://temp',
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGetMetadataWithKey()
    {
        $fixture = new Stream(uniqid());

        $actual = $fixture->getMetadata('uri');

        $this->assertEquals('php://temp', $actual);
    }

    public function testGetMetadataWithUnknownKey()
    {
        $fixture = new Stream(uniqid());

        $actual = $fixture->getMetadata(uniqid());

        $this->assertNull($actual);
    }

    public function testGetMetadataWhenNotAttached()
    {
        $fixture = new Stream(uniqid());
        $fixture->close();

        $actual = $fixture->getMetadata();

        $this->assertNull($actual);
    }
}
