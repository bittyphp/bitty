<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Collection;
use Bizurkur\Bitty\CollectionInterface;
use Bizurkur\Bitty\Http\AbstractMessage;
use Bizurkur\Bitty\Http\RequestBody;
use Bizurkur\Bitty\Http\UploadedFile;
use Bizurkur\Bitty\Http\UploadedFileCollection;
use Bizurkur\Bitty\Http\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class Request extends AbstractMessage implements ServerRequestInterface
{
    /**
     * HTTP method being used, e.g. GET, POST, etc.
     *
     * @var string
     */
    protected $method = null;

    /**
     * Valid HTTP methods.
     *
     * Updated 2017-12-22
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
     *
     * @var string[]
     */
    protected $validMethods = [
        'OPTIONS',
        'HEAD',
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'TRACE',
        'CONNECT',
    ];

    /**
     * URI of the request.
     *
     * @var UriInterface
     */
    protected $uri = null;

    /**
     * Query parameters.
     *
     * @var CollectionInterface
     */
    protected $query = null;

    /**
     * Request parameters.
     *
     * @var CollectionInterface
     */
    protected $request = null;

    /**
     * Request attributes.
     *
     * @var CollectionInterface
     */
    protected $attributes = null;

    /**
     * Cookie parameters.
     *
     * @var CollectionInterface
     */
    protected $cookies = null;

    /**
     * Uploaded files.
     *
     * @var CollectionInterface
     */
    protected $files = null;

    /**
     * Server parameters.
     *
     * @var CollectionInterface
     */
    protected $server = null;

    /**
     * HTTP request target.
     *
     * @var string
     */
    protected $requestTarget = null;

    /**
     * Parsed request body.
     *
     * @var null|array|object
     */
    protected $parsedBody = null;

    /**
     * List of callables to parse different content types.
     *
     * @var callable[]
     */
    protected $contentTypeParsers = [];

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array $headers
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param UploadedFileInterface[] $files
     * @param array $server
     * @param StreamInterface|resource|string $body
     */
    public function __construct(
        $method = 'GET',
        $uri = '',
        array $headers = [],
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $body = ''
    ) {
        $this->method = $this->filterMethod($method);
        $this->uri = Uri::createFromString($uri);
        $this->headers = $this->filterHeaders($headers);
        $this->query = $this->filterQueryParams($query);
        $this->request = $this->filterRequestParams($request);
        $this->attributes = $this->filterAttributes($attributes);
        $this->cookies = $this->filterCookieParams($cookies);
        $this->files = $this->filterFileParams($files);
        $this->server = $this->filterServerParams($server);
        $this->body = $this->filterBody($body);

        $this->protocolVersion = $this->filterProtocolVersion(
            str_replace('HTTP/', '', $this->server->get('SERVER_PROTOCOL', '1.1'))
        );

        $this->registerContentTypeParser('application/json', function ($body) {
            $json = json_decode($body, true);
            if (!is_array($json)) {
                return null;
            }

            return $json;
        });

        $this->registerContentTypeParser('application/x-www-form-urlencoded', function ($body) {
            parse_str($body, $data);

            return $data;
        });
    }

    public function __clone()
    {
        // $this->headers = clone $this->headers;
        $this->uri = clone $this->uri;
        $this->query = clone $this->query;
        $this->request = clone $this->request;
        $this->attributes = clone $this->attributes;
        $this->cookies = clone $this->cookies;
        $this->files = clone $this->files;
        $this->server = clone $this->server;
        $this->body = clone $this->body;
    }

    /**
     * Creates a new request from an array.
     *
     * @param array $data
     *
     * @return static
     */
    public static function createFromArray(array $data)
    {
        return new static(
            isset($data['method']) ? $data['method'] : 'GET',
            isset($data['uri']) ? $data['uri'] : new Uri(),
            isset($data['headers']) ? $data['headers'] : [],
            isset($data['query']) ? $data['query'] : [],
            isset($data['request']) ? $data['request'] : [],
            isset($data['attributes']) ? $data['attributes'] : [],
            isset($data['cookies']) ? $data['cookies'] : [],
            isset($data['files']) ? $data['files'] : [],
            isset($data['server']) ? $data['server'] : [],
            isset($data['body']) ? $data['body'] : ''
        );
    }

    /**
     * Creates a new request from global variables.
     *
     * @return static
     */
    public static function createFromGlobals()
    {
        $server = new ServerCollection($_SERVER);
        $method = $server->get('REQUEST_METHOD', 'GET');
        $uri = Uri::createFromEnvironment($server);
        $headers = $server->getHeaders();
        $body = new RequestBody();
        $files = new UploadedFileCollection($_FILES);

        return new static($method, $uri, $headers, $_GET, $_POST, [], $_COOKIE, $files->all(), $_SERVER, $body);
    }

    /**
     * Gets a query parameter.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function query($name, $default = null)
    {
        return $this->query->get($name, $default);
    }

    /**
     * Gets a request parameter.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function request($name, $default = null)
    {
        return $this->request->get($name, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget()
    {
        if (null === $this->requestTarget) {
            return $this->uri->getRequestTarget();
        }

        return $this->requestTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget)
    {
        $request = clone $this;
        $request->requestTarget = $this->filterRequestTarget($requestTarget);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method)
    {
        $request = clone $this;
        $request->method = $method;

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri()
    {
        return clone $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $request = clone $this;
        $request->uri = $uri;

        if ($preserveHost) {
            if ('' === $this->getHeaderLine('Host') && '' !== $uri->getHost()) {
                $request->headers['Host'] = [$uri->getHost()];
            }
        } elseif ('' !== $uri->getHost()) {
            $request->headers['Host'] = [$uri->getHost()];
        }

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams()
    {
        return $this->query->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query)
    {
        $request = clone $this;
        $request->query = $this->filterQueryParams($query);

        return $request;
    }

    /**
     * Gets the request parameters.
     *
     * This should be formatted similar to $_POST.
     *
     * @return array
     */
    public function getRequestParams()
    {
        return $this->request->all();
    }

    /**
     * Sets request parameters.
     *
     * This should be formatted similar to $_POST.
     *
     * @param array $request
     *
     * @return static
     */
    public function withRequestParams(array $request)
    {
        $request = clone $this;
        $request->request = $this->filterRequestParams($request);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams()
    {
        return $this->cookies->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies)
    {
        $request = clone $this;
        $request->cookies = $this->filterCookieParams($cookies);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles()
    {
        $files = [];
        foreach ($this->files->all() as $file) {
            $files[] = clone $file;
        }

        return $files;
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $files)
    {
        $request = clone $this;
        $request->files = $this->filterFileParams($files);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getServerParams()
    {
        return $this->server->all();
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        $contentTypes = $this->getHeader('Content-Type');

        if ('POST' === $this->method
            && (
                in_array('application/x-www-form-urlencoded', $contentTypes)
                || in_array('multipart/form-data', $contentTypes)
            )
        ) {
            return $this->request->all();
        }

        if (null === $this->parsedBody) {
            $body = (string) $this->body;

            foreach ($contentTypes as $contentType) {
                if (!isset($this->contentTypeParsers[$contentType])) {
                    continue;
                }

                $parsedBody = $this->contentTypeParsers[$contentType]($body);
                $this->parsedBody = $this->filterParsedBody($parsedBody);

                return $this->parsedBody;
            }
        }

        return $this->parsedBody;
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($parsedBody)
    {
        $request = clone $this;
        $request->parsedBody = $this->filterParsedBody($parsedBody);

        return $request;
    }

    /**
     * Registers a callback to parse the specific content type.
     *
     * @param string $contentType
     * @param callable $callback
     */
    public function registerContentTypeParser($contentType, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Callback for "%s" must be a callable; %s given.',
                    $contentType,
                    gettype($callback)
                )
            );
        }

        $this->contentTypeParsers[(string) $contentType] = $callback;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes()
    {
        return $this->attributes->all();
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value)
    {
        $attributes = $this->attributes->all();
        $attributes[$name] = $value;

        $request = clone $this;
        $request->attributes = $this->filterAttributes($attributes);

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name)
    {
        $attributes = $this->attributes->all();

        unset($attributes[$name]);

        $request = clone $this;
        $request->attributes = $this->filterAttributes($attributes);

        return $request;
    }

    /**
     * Filters HTTP method to make sure it's valid.
     *
     * @param string $method
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterMethod($method)
    {
        if (!in_array($method, $this->validMethods)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'HTTP method "%s" is invalid. Valid methods are: ["%s"]',
                    $method,
                    implode('", "', $this->validMethods)
                )
            );
        }

        return $method;
    }

    /**
     * Filters request target to make sure it's valid.
     *
     * @param string $requestTarget
     *
     * @return string
     */
    protected function filterRequestTarget($requestTarget)
    {
        return (string) $requestTarget;
    }

    /**
     * Filters query parameters to make sure they're valid.
     *
     * @param string $query
     *
     * @return Collection
     */
    protected function filterQueryParams(array $query)
    {
        return new Collection($query);
    }

    /**
     * Filters request parameters to make sure they're valid.
     *
     * @param string $request
     *
     * @return Collection
     */
    protected function filterRequestParams(array $request)
    {
        return new Collection($request);
    }

    /**
     * Filters attributes to make sure they're valid.
     *
     * @param string $attributes
     *
     * @return Collection
     */
    protected function filterAttributes(array $attributes)
    {
        return new Collection($attributes);
    }

    /**
     * Filters cookie parameters to make sure they're valid.
     *
     * @param string $cookies
     *
     * @return Collection
     */
    protected function filterCookieParams(array $cookies)
    {
        return new Collection($cookies);
    }

    /**
     * Filters file parameters to make sure they're valid.
     *
     * @param string $files
     *
     * @return Collection
     */
    protected function filterFileParams(array $files)
    {
        return new Collection($files);
    }

    /**
     * Filters server parameters to make sure they're valid.
     *
     * @param string $server
     *
     * @return ServerCollection
     */
    protected function filterServerParams(array $server)
    {
        return new ServerCollection($server);
    }

    /**
     * Filters parsed body to make sure it's valid.
     *
     * @param null|array|object $server
     *
     * @return null|array|object
     *
     * @throws \InvalidArgumentException
     */
    protected function filterParsedBody($parsedBody)
    {
        if (!is_null($parsedBody) && !is_array($parsedBody) && !is_object($parsedBody)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Parsed body must be an array, object, or null; %s given.',
                    gettype($parsedBody)
                )
            );
        }

        return $parsedBody;
    }
}
