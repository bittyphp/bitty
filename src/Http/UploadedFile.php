<?php

namespace Bitty\Http;

use Bitty\Http\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * File contents stream.
     *
     * @var StreamInterface
     */
    protected $stream = null;

    /**
     * Full path to the file.
     *
     * @var string
     */
    protected $path = null;

    /**
     * Name of the file.
     *
     * @var string|null
     */
    protected $name = null;

    /**
     * Media-type of the file.
     *
     * @var string|null
     */
    protected $mediaType = null;

    /**
     * Size of the file.
     *
     * @var int|null
     */
    protected $size = null;

    /**
     * Error associated to the file.
     *
     * @var int
     */
    protected $error = null;

    /**
     * Whether it came from a SAPI environment or not.
     *
     * @var bool
     */
    protected $sapi = null;

    /**
     * @param string $path
     * @param string|null $name
     * @param string|null $mediaType
     * @param int|null $size
     * @param int $error
     * @param bool $sapi
     */
    public function __construct(
        $path,
        $name = null,
        $mediaType = null,
        $size = null,
        $error = UPLOAD_ERR_OK,
        $sapi = false
    ) {
        $this->path      = $path;
        $this->name      = $name;
        $this->mediaType = $mediaType;
        $this->size      = $size;
        $this->error     = $error;
        $this->sapi      = $sapi;
    }

    /**
     * {@inheritDoc}
     */
    public function getStream()
    {
        if (null === $this->path) {
            throw new \RuntimeException(
                'Stream not available; the file appears to have been moved.'
            );
        }

        if (null === $this->stream) {
            // lazy load, since we don't always need it
            $this->stream = new Stream(fopen($this->path, 'r'));
        }

        return $this->stream;
    }

    /**
     * {@inheritDoc}
     */
    public function moveTo($targetPath)
    {
        if (null === $this->path) {
            throw new \RuntimeException(
                'Unable to perform move; the file has already been moved.'
            );
        }

        if (is_resource($targetPath)) {
            // target is a stream
            $stream = $this->getStream();
            if (false === stream_copy_to_stream($stream, $targetPath, -1, 0)) {
                throw new \RuntimeException('Failed to move file to stream.');
            }
        } elseif (false !== strpos($targetPath, '://')) {
            // target appears to be a URL
            if (!copy($this->path, $targetPath)) {
                throw new \RuntimeException(
                    sprintf('Failed to move file to "%s"', $targetPath)
                );
            }

            if (!unlink($this->path)) {
                throw new \RuntimeException('Failed to remove file after move.');
            }
        } else {
            // target is a local path
            if (!is_writable(dirname($targetPath))) {
                throw new \InvalidArgumentException(
                    sprintf('Target path "%s" is not writable!', $targetPath)
                );
            }

            if ($this->sapi) {
                // SAPI environment
                if (!is_uploaded_file($this->path)) {
                    throw new \RuntimeException('File is not a valid uploaded file.');
                }

                if (!move_uploaded_file($this->path, $targetPath)) {
                    throw new \RuntimeException(
                        sprintf('Failed to move file to "%s"', $targetPath)
                    );
                }
            } else {
                // Non-SAPI environment
                if (!rename($this->path, $targetPath)) {
                    throw new \RuntimeException(
                        sprintf('Failed to move file to "%s"', $targetPath)
                    );
                }
            }
        }

        $this->path = null;

        if (null !== $this->stream) {
            $this->stream->close();
            $this->stream = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientMediaType()
    {
        return $this->mediaType;
    }
}
