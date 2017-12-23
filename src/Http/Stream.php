<?php

namespace Bizurkur\Bitty\Http;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * Stream of data.
     *
     * @var resource
     */
    protected $stream = null;

    /**
     * @param resource|string $stream
     */
    public function __construct($stream)
    {
        if (is_resource($stream)) {
            $this->stream = $stream;
        } elseif (is_string($stream)) {
            $this->stream = fopen('php://temp', 'w+');
            fwrite($this->stream, $stream);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s must be constructed with a resource or string; %s given.',
                    __CLASS__,
                    gettype($stream)
                )
            );
        }

        rewind($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $string = stream_get_contents($this->stream, -1, 0);
        if (false === $string) {
            return '';
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        fclose($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $stream = $this->stream;

        $this->stream = null;

        return $stream;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        $stats = fstat($this->stream);

        if (!isset($stats['size'])) {
            return null;
        }

        return $stats['size'];
    }

    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        $position = ftell($this->stream);
        if (false === $position) {
            throw new \RuntimeException(
                sprintf('Unable to get position of stream.')
            );
        }

        return $position;
    }

    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable()
    {
        $seekable = $this->getMetadata('seekable');
        if (null === $seekable) {
            return false;
        }

        return $seekable;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (0 > fseek($this->stream, $offset, $whence)) {
            throw new \RuntimeException(
                sprintf('Failed to seek to offset %s.', $offset)
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        if (!rewind($this->stream)) {
            throw new \RuntimeException('Failed to rewind stream.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable()
    {
        $mode = $this->getMetadata('mode');
        if (null === $mode) {
            return false;
        }

        $mode = str_replace(['b', 'e'], '', $mode);

        return in_array($mode, ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+']);
    }

    /**
     * {@inheritDoc}
     */
    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }

        $bytes = fwrite($this->stream, $string);
        if (false === $bytes) {
            throw new \RuntimeException('Failed to write to stream.');
        }

        return $bytes;
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable()
    {
        $mode = $this->getMetadata('mode');
        if (null === $mode) {
            return false;
        }

        $mode = str_replace(['b', 'e'], '', $mode);

        return in_array($mode, ['r', 'r+', 'w+', 'a+', 'x+', 'c+']);
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable.');
        }

        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new \RuntimeException('Failed to read from stream.');
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        $string = stream_get_contents($this->stream);
        if (false === $string) {
            throw new \RuntimeException('Failed to get contents of stream.');
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->stream);
        if (null === $key) {
            return $metadata;
        }

        if (isset($metadata[$key]) || array_key_exists($key, $metadata)) {
            return $metadata[$key];
        }

        return null;
    }
}
