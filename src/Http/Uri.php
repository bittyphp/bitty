<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Http\Parameters;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * Default ports.
     *
     * @var int[]
     */
    protected $defaultPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp' => 21,
    ];

    /**
     * URI scheme.
     *
     * @var string
     */
    protected $scheme = null;

    /**
     * Authority username.
     *
     * @var string
     */
    protected $user = null;

    /**
     * Authority password.
     *
     * @var string
     */
    protected $pass = null;

    /**
     * URI hostname.
     *
     * @var string
     */
    protected $host = null;

    /**
     * URI port number.
     *
     * @var int
     */
    protected $port = null;

    /**
     * URI path.
     *
     * @var string
     */
    protected $path = null;

    /**
     * URI query string.
     *
     * @var string
     */
    protected $query = null;

    /**
     * URI fragment.
     *
     * @var string
     */
    protected $fragment = null;

    /**
     * @param string $scheme
     * @param string $user
     * @param string $pass
     * @param string $host
     * @param int $port
     * @param string $path
     * @param string $query
     * @param string $fragment
     */
    public function __construct(
        $scheme = '',
        $host = '',
        $port = 0,
        $path = '',
        $query = '',
        $fragment = '',
        $user = '',
        $pass = ''
    ) {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $this->filterHost($host);
        $this->port = $this->filterPort($port);
        $this->path = $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterFragment($fragment);
        $this->user = (string) $user;
        $this->pass = (string) $pass;
    }

    /**
     * Creates a new URI from a string or object that can be cast as a string.
     *
     * @param string|object $uri
     *
     * @return static
     */
    public static function createFromString($uri)
    {
        $data = parse_url((string) $uri);
        if (false === $data) {
            return new Uri();
        }

        return static::createFromArray($data);
    }

    /**
     * Creates a new URI from an array.
     *
     * Array keys should be identical to that of PHP's parse_url().
     *
     * @param mixed[] $data
     *
     * @return static
     */
    public static function createFromArray(array $data)
    {
        return new static(
            isset($data['scheme']) ? $data['scheme'] : '',
            isset($data['host']) ? $data['host'] : '',
            isset($data['port']) ? $data['port'] : '',
            isset($data['path']) ? $data['path'] : '',
            isset($data['query']) ? $data['query'] : '',
            isset($data['fragment']) ? $data['fragment'] : '',
            isset($data['user']) ? $data['user'] : '',
            isset($data['pass']) ? $data['pass'] : ''
        );
    }

    /**
     * Creates a new URI from parameters.
     *
     * The parameters are expected to have the same keys as $_SERVER would.
     *
     * @param Parameters $params
     *
     * @return static
     */
    public static function createFromParameters(Parameters $params)
    {
        $scheme = 'http';
        $isHttps = $params->get('HTTPS');
        if (!empty($isHttps) && 'off' !== strtolower($isHttps)) {
            $scheme = 'https';
        }

        $user = $params->get('PHP_AUTH_USER', '');
        $pass = $params->get('PHP_AUTH_PW', '');
        $port = $params->get('SERVER_PORT');

        if ($params->has('HTTP_HOST')) {
            $host = $params->get('HTTP_HOST');
            if (false !== ($pos = strrpos($host, ':'))) {
                $port = substr($host, $pos + 1);
                $host = substr($host, 0, $pos);
            }
        } else {
            $host = $params->get('SERVER_NAME');
        }

        $path = parse_url($params->get('REQUEST_URI', ''), PHP_URL_PATH);
        if (empty($path)) {
            $path = $params->get('PATH_INFO');
        }

        $query = $params->get('QUERY_STRING', '');
        if (empty($query)) {
            $query = parse_url($params->get('REQUEST_URI', ''), PHP_URL_QUERY);
        }

        return new static($scheme, $host, $port, $path, $query, '', $user, $pass);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $string = '';

        if (!empty($this->scheme)) {
            $string .= $this->scheme.':';
        }

        $authority = $this->getAuthority();
        if (!empty($authority) || 'file' === $this->scheme) {
            // 'file' is special and doesn't need a host
            $string .= '//'.$authority;
        }

        $string .= '/'.ltrim($this->getPath(), '/');

        if (!empty($this->query)) {
            $string .= '?'.$this->query;
        }

        if (!empty($this->fragment)) {
            $string .= '#'.$this->fragment;
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme()
    {
        if (empty($this->scheme)) {
            return '';
        }

        return $this->scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority()
    {
        $string = '';

        $userInfo = $this->getUserInfo();
        if (!empty($userInfo)) {
            $string .= $userInfo.'@';
        }

        if (!empty($this->host)) {
            $string .= $this->host;
        }

        $port = $this->getPort();
        if (null !== $port) {
            $string .= ':'.$port;
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo()
    {
        if (empty($this->user)) {
            return '';
        }

        $string = $this->user;
        if (!empty($this->pass)) {
            $string .= ':'.$this->pass;
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost()
    {
        if (empty($this->host)) {
            return '';
        }

        return $this->host;
    }

    /**
     * {@inheritDoc}
     */
    public function getPort()
    {
        if (empty($this->port)) {
            return null;
        }

        if (isset($this->defaultPorts[$this->scheme])
            && $this->defaultPorts[$this->scheme] === $this->port
        ) {
            return null;
        }

        return $this->port;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        if (empty($this->path)) {
            return '';
        }

        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery()
    {
        if (empty($this->query)) {
            return '';
        }

        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment()
    {
        if (empty($this->fragment)) {
            return '';
        }

        return $this->fragment;
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme($scheme)
    {
        if ($this->scheme === $scheme) {
            return $this;
        }

        $uri = clone $this;
        $uri->scheme = $this->filterScheme($scheme);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo($user, $password = null)
    {
        if ($this->user === $user && $this->pass === $password) {
            return $this;
        }

        $uri = clone $this;
        $uri->user = $user;
        $uri->pass = $password;

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withHost($host)
    {
        if ($this->host === $host) {
            return $this;
        }

        $uri = clone $this;
        $uri->host = $this->filterHost($host);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withPort($port)
    {
        if ($this->port === (int) $port) {
            return $this;
        }

        $uri = clone $this;
        $uri->port = $this->filterPort($port);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath($path)
    {
        if ($this->path === $path) {
            return $this;
        }

        $uri = clone $this;
        $uri->path = $this->filterPath($path);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery($query)
    {
        if ($this->query === $query) {
            return $this;
        }

        $uri = clone $this;
        $uri->query = $this->filterQuery($query);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment($fragment)
    {
        if ($this->fragment === $fragment) {
            return $this;
        }

        $uri = clone $this;
        $uri->fragment = $this->filterFragment($fragment);

        return $uri;
    }

    /**
     * Filters a scheme to make sure it's valid.
     *
     * @param string $scheme
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterScheme($scheme)
    {
        $scheme = rtrim(strtolower((string) $scheme), ':');
        if (!empty($scheme) && !preg_match('/^[a-z][a-z0-9\+\.-]*$/', $scheme)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid scheme "%s".', $scheme)
            );
        }

        return $scheme;
    }

    /**
     * Filters a host to make sure it's valid.
     *
     * @param string $host
     *
     * @return string
     */
    protected function filterHost($host)
    {
        return strtolower((string) $host);
    }

    /**
     * Filters a port to make sure it's valid.
     *
     * @param int $port
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function filterPort($port)
    {
        $port = (int) $port;

        // allow zero as an empty check
        if (0 > $port || 65535 < $port) {
            throw new \InvalidArgumentException(
                'Invalid port %d. Must be between 1 and 65,535.',
                $port
            );
        }

        return $port;
    }

    /**
     * Filters a path to make sure it's valid.
     *
     * @param string $path
     *
     * @return string
     */
    protected function filterPath($path)
    {
        return implode('/',
            array_map('rawurlencode',
                array_map('rawurldecode',
                    explode('/', (string) $path)
                )
            )
        );
    }

    /**
     * Filters a query string to make sure it's valid.
     *
     * @param string $query
     *
     * @return string
     */
    protected function filterQuery($query)
    {
        $params = explode('&', ltrim((string) $query, '?'));

        $len = count($params);
        for ($i = 0; $i < $len; $i++) {
            if (empty($params[$i])) {
                continue;
            }

            $params[$i] = implode('=',
                array_map('rawurlencode',
                    array_map('rawurldecode',
                        explode('=', $params[$i], 2)
                    )
                )
            );
        }

        return implode('&', $params);
    }

    /**
     * Filters a fragment to make sure it's valid.
     *
     * @param string $fragment
     *
     * @return string
     */
    protected function filterFragment($fragment)
    {
        return rawurlencode(
            rawurldecode(
                ltrim((string) $fragment, '#')
            )
        );
    }
}
