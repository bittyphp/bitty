<?php

namespace Bitty\Http;

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
        if (!$this->isAttached()) {
            return '';
        }

        $string = stream_get_contents($this->stream, -1, 0);

        return (string) $string;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if (!$this->isAttached()) {
            return;
        }

        fclose($this->stream);
        $this->stream = null;
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
        if (!$this->isAttached()) {
            return null;
        }

        $stats = fstat($this->stream);

        return isset($stats['size']) ? $stats['size'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        if (!$this->isAttached()
            || false === ($position = ftell($this->stream))
        ) {
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
        return $this->isAttached() ? feof($this->stream) : true;
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable()
    {
        if (!$this->isAttached()
            || null === ($seekable = $this->getMetadata('seekable'))
        ) {
            return false;
        }

        return $seekable;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable.');
        }

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
        if (!$this->isAttached() || !rewind($this->stream)) {
            throw new \RuntimeException('Failed to rewind stream.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable()
    {
        if (!$this->isAttached()
            || null === ($mode = $this->getMetadata('mode'))
        ) {
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
        if (!$this->isWritable()
            || false === ($bytes = fwrite($this->stream, $string))
        ) {
            throw new \RuntimeException('Failed to write to stream.');
        }

        return $bytes;
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable()
    {
        if (!$this->isAttached()
            || null === ($mode = $this->getMetadata('mode'))
        ) {
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
        if (!$this->isReadable()
            || false === ($string = fread($this->stream, $length))
        ) {
            throw new \RuntimeException('Failed to read from stream.');
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        if (!$this->isAttached()
            || false === ($string = stream_get_contents($this->stream))
        ) {
            throw new \RuntimeException('Failed to get contents of stream.');
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        if (!$this->isAttached()) {
            return null;
        }

        $metadata = stream_get_meta_data($this->stream);
        if (null === $key) {
            return $metadata;
        }

        if (isset($metadata[$key]) || array_key_exists($key, $metadata)) {
            return $metadata[$key];
        }

        return null;
    }

    /**
     * Checks if the stream is attached.
     *
     * @return bool
     */
    protected function isAttached()
    {
        return null !== $this->stream;
    }
}
