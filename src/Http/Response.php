<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Http\AbstractMessage;
use Bizurkur\Bitty\Http\Cookie;
use Bizurkur\Bitty\Http\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends AbstractMessage implements ResponseInterface
{
    /**
     * Valid HTTP status codes and reasons.
     *
     * Updated 2017-12-22
     *
     * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @var string[]
     */
    protected $statusCodeList = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $statusCode = null;

    /**
     * HTTP reason phrase.
     *
     * @var string
     */
    protected $reasonPhrase = null;

    /**
     * Array of cookies to set.
     *
     * @var Cookie[]
     */
    protected $cookies = [];

    /**
     * @param StreamInterface|string $body
     * @param int $statusCode
     * @param string[] $headers
     * @param Cookie[] $cookies
     * @param string $reasonPhrase
     * @param string $protocolVersion
     */
    public function __construct(
        $body = '',
        $statusCode = 200,
        $headers = [],
        $cookies = [],
        $reasonPhrase = '',
        $protocolVersion = '1.0'
    ) {
        if ($body instanceof StreamInterface) {
            $this->body = $body;
        } else {
            $this->body = new Stream($body);
        }

        $this->cookies = [];
        foreach ($cookies as $cookie) {
            if ($cookie instanceof Cookie) {
                $name = $cookie->getName();
                $this->cookies[$name] = $cookie;
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Cookie must be an instance of "%s"; %s given.',
                        Cookie::class,
                        gettype($cookie)
                    )
                );
            }
        }

        $this->headers = [];
        foreach ($headers as $header => $values) {
            $this->validateHeader($header, $values);
            $this->headers[$header] = (array) $values;
        }

        if (!isset($this->statusCodeList[$statusCode])) {
            throw new \InvalidArgumentException(
                sprintf('Unknown HTTP status code "%s"', $statusCode)
            );
        }

        if (empty($reasonPhrase)) {
            $reasonPhrase = $this->statusCodeList[$statusCode];
        }

        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->createFromArray([
            'statusCode' => $code,
            'reasonPhrase' => $reasonPhrase,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Gets the list of cookies.
     *
     * @return string[]
     */
    public function getCookies()
    {
        $cookies = [];
        foreach ($this->cookies as $name => $cookie) {
            $cookies[$name] = (string) $cookie;
        }

        return $cookies;
    }

    /**
     * Checks if a cookie has been set.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCookie($name)
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Gets a cookie, if it exists.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getCookie($name)
    {
        if (!isset($this->cookies[$name])) {
            return null;
        }

        return (string) $this->cookies[$name];
    }

    /**
     * Builds a new response, adding the given cookie.
     *
     * @param Cookie $cookie
     *
     * @return static
     */
    public function withCookie(Cookie $cookie)
    {
        $cookies = [];
        foreach ($this->cookies as $name => $originalCookie) {
            $cookies[$name] = clone $originalCookie;
        }

        $cookies[$cookie->getName()] = $cookie;

        return $this->createFromArray(['cookies' => $cookies]);
    }

    /**
     * Builds a new response, removing the given cookie.
     *
     * @param string $cookie
     *
     * @return static
     */
    public function withoutCookie($name)
    {
        $cookies = [];
        foreach ($this->cookies as $key => $cookie) {
            if (0 === strcasecmp($key, $name)) {
                continue;
            }

            $cookies[$name] = clone $cookie;
        }

        return $this->createFromArray(['cookies' => $cookies]);
    }

    /**
     * Sends the response headers.
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        header(
            sprintf(
                'HTTP/%s %s %s',
                $this->protocolVersion,
                $this->statusCode,
                $this->reasonPhrase
            ),
            true,
            $this->statusCode
        );

        foreach ($this->headers as $header => $values) {
            foreach ($values as $value) {
                header(sprintf("%s: %s", $header, $value), false);
            }
        }

        foreach ($this->cookies as $cookie) {
            if ($cookie->isRaw()) {
                setrawcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpires(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->getSecure(),
                    $cookie->getHttpOnly()
                );
            } else {
                setcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpires(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->getSecure(),
                    $cookie->getHttpOnly()
                );
            }
        }
    }

    /**
     * Sends the response body.
     */
    public function sendBody()
    {
        echo (string) $this->body;
    }

    /**
     * Sends the response.
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Creates a new response from an array.
     *
     * @param mixed[] $data
     *
     * @return static
     */
    protected function createFromArray(array $data)
    {
        $data += [
            'body' => $this->body,
            'headers' => $this->headers,
            'cookies' => $this->cookies,
            'statusCode' => $this->statusCode,
            'reasonPhrase' => $this->reasonPhrase,
            'protocolVersion' => $this->protocolVersion,
        ];

        return new static(
            $data['body'],
            $data['statusCode'],
            $data['headers'],
            $data['cookies'],
            $data['reasonPhrase'],
            $data['protocolVersion']
        );
    }
}
