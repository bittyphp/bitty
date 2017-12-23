<?php

namespace Bizurkur\Bitty\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * HTTP response body.
     *
     * @var StreamInterface
     */
    protected $body = null;

    /**
     * HTTP headers.
     *
     * @var string[]
     */
    protected $headers = null;

    /**
     * HTTP protocol version.
     *
     * @var string
     */
    protected $protocolVersion = null;

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion($version)
    {
        return $this->createFromArray(['protocolVersion' => $version]);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader($name)
    {
        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($name)
    {
        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                return $values;
            }
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader($name, $value)
    {
        $headers = [];
        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                $headers[$name] = $value;

                continue;
            }

            $headers[$header] = $values;
        }

        $headers[$name] = $value;

        return $this->createFromArray(['headers' => $headers]);
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader($name, $value)
    {
        $headers = [];
        $found = false;
        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                $found = true;
                foreach ((array) $value as $newValue) {
                    $values[] = $newValue;
                }
            }

            $headers[$header] = $values;
        }

        if (!$found) {
            $headers[$name] = $value;
        }

        return $this->createFromArray(['headers' => $headers]);
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader($name)
    {
        $headers = [];
        foreach ($this->headers as $header => $values) {
            if (0 === strcasecmp($name, $header)) {
                continue;
            }

            $headers[$header] = $values;
        }

        return $this->createFromArray(['headers' => $headers]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body)
    {
        return $this->createFromArray(['body' => $body]);
    }

    /**
     * Validates a header name and values.
     *
     * @param string $header
     * @param string|string[] $values
     *
     * @throws \InvalidArgumentException
     */
    protected function validateHeader($header, $values = [])
    {
        // TODO: validate $name
        // throw new \InvalidArgumentException()

        if (!is_string($values) && !is_array($values)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Values for header "%s" must be a string or array; %s given.',
                    $header,
                    gettype($values)
                )
            );
        }

        foreach ((array) $values as $value) {
            if (is_string($value)) {
                continue;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Values for header "%s" must contain only strings; %s given.',
                    $header,
                    gettype($value)
                )
            );
        }
    }

    /**
     * Creates a new message from an array.
     *
     * @param mixed[] $data
     *
     * @return static
     */
    abstract protected function createFromArray(array $data);
}
