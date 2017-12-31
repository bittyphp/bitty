<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\CollectionInterface;
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
     * @param string $uri
     */
    public function __construct($uri = '')
    {
        $data = parse_url($uri);
        if (false === $data) {
            return;
        }

        $data += [
            'scheme' => '',
            'host' => '',
            'port' => '',
            'path' => '',
            'query' => '',
            'fragment' => '',
            'user' => '',
            'pass' => '',
        ];

        $this->scheme   = $this->filterScheme($data['scheme']);
        $this->host     = $this->filterHost($data['host']);
        $this->port     = $this->filterPort($data['port']);
        $this->path     = $this->filterPath($data['path']);
        $this->query    = $this->filterQuery($data['query']);
        $this->fragment = $this->filterFragment($data['fragment']);
        $this->user     = (string) $data['user'];
        $this->pass     = (string) $data['pass'];
    }

    /**
     * Creates a new URI from environment data.
     *
     * The data is expected to have the same keys as $_SERVER would.
     *
     * @param CollectionInterface $env
     *
     * @return static
     */
    public static function createFromEnvironment(CollectionInterface $env)
    {
        $scheme  = 'http';
        $isHttps = $env->get('HTTPS');
        if (!empty($isHttps) && 'off' !== strtolower($isHttps)) {
            $scheme = 'https';
        }

        $user = $env->get('PHP_AUTH_USER');
        $pass = $env->get('PHP_AUTH_PW');
        $port = $env->get('SERVER_PORT');

        if ($env->has('HTTP_HOST')) {
            $host = $env->get('HTTP_HOST');
            if (false !== ($pos = strrpos($host, ':'))) {
                $port = substr($host, $pos + 1);
                $host = substr($host, 0, $pos);
            }
        } else {
            $host = $env->get('SERVER_NAME');
        }

        $path = parse_url($env->get('REQUEST_URI'), PHP_URL_PATH);
        if (empty($path)) {
            $path = $env->get('PATH_INFO');
        }

        $query = parse_url($env->get('REQUEST_URI'), PHP_URL_QUERY);
        if (empty($query)) {
            $query = $env->get('QUERY_STRING');
        }

        $uri = new static();

        return $uri->withScheme($scheme)
            ->withUserInfo($user, $pass)
            ->withHost($host)
            ->withPort($port)
            ->withPath($path)
            ->withQuery($query);
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

        $string .= $this->getRequestTarget();

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
        } elseif ('http' === $this->scheme || 'https' === $this->scheme) {
            $string .= 'localhost';
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
     * Gets the request target.
     *
     * This is the path and query string combined.
     *
     * @return string
     */
    public function getRequestTarget()
    {
        $string = '/'.ltrim($this->getPath(), '/');

        if (!empty($this->query)) {
            $string .= '?'.$this->query;
        }

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme($scheme)
    {
        $uri = clone $this;

        $uri->scheme = $this->filterScheme($scheme);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo($user, $password = null)
    {
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
        $uri = clone $this;

        $uri->host = $this->filterHost($host);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withPort($port)
    {
        $uri = clone $this;

        $uri->port = $this->filterPort($port);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath($path)
    {
        $uri = clone $this;

        $uri->path = $this->filterPath($path);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery($query)
    {
        $uri = clone $this;

        $uri->query = $this->filterQuery($query);

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment($fragment)
    {
        $uri = clone $this;

        $uri->fragment = $this->filterFragment($fragment);

        return $uri;
    }

    /**
     * Sets the request target.
     *
     * This is the path and query string combined.
     *
     * @param string $requestTarget
     *
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $tmp = new static($requestTarget);
        $uri = clone $this;

        $uri->path  = $this->filterPath($tmp->getPath());
        $uri->query = $this->filterQuery($tmp->getQuery());

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
                sprintf(
                    'Invalid port %d. Must be between 1 and 65,535.',
                    $port
                )
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
        return implode(
            '/',
            array_map(
                'rawurlencode',
                array_map(
                    'rawurldecode',
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

            $params[$i] = implode(
                '=',
                array_map(
                    'rawurlencode',
                    array_map(
                        'rawurldecode',
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
