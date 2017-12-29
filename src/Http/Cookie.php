<?php

namespace Bizurkur\Bitty\Http;

class Cookie
{
    /**
     * The cookie name.
     *
     * @var string
     */
    protected $name = null;

    /**
     * The cookie value.
     *
     * @var string
     */
    protected $value = null;

    /**
     * The cookie expiration time, in seconds.
     *
     * @var int
     */
    protected $expire = null;

    /**
     * The cookie URI path.
     *
     * @var string
     */
    protected $path = null;

    /**
     * The cookie domain.
     *
     * @var string
     */
    protected $domain = null;

    /**
     * Whether the cookie is for HTTPS or not.
     *
     * @var bool
     */
    protected $secure = null;

    /**
     * Whether the cookie can be accessed by JavaScript or not.
     *
     * @var bool
     */
    protected $httpOnly = null;

    /**
     * Whether or not the cookie should be written raw.
     *
     * @var bool
     */
    protected $raw = null;

    /**
     * @param string $name The cookie name.
     * @param string $value The cookie value.
     * @param int $expire The cookie expiration time.
     * @param string $path The cookie URI path.
     * @param string $domain The cookie domain.
     * @param bool $secure Whether the cookie is for HTTPS or not.
     * @param bool $httpOnly Disallow script access or not.
     * @param bool $raw Whether or not the cookie should be written raw.
     */
    public function __construct(
        $name,
        $value = '',
        $expire = 0,
        $path = '',
        $domain = '',
        $secure = false,
        $httpOnly = true,
        $raw = false
    ) {
        $this->name     = (string) $name;
        $this->value    = (string) $value;
        $this->path     = (string) $path;
        $this->domain   = (string) $domain;
        $this->secure   = (bool) $secure;
        $this->httpOnly = (bool) $httpOnly;
        $this->expire   = (int) $expire;
        $this->raw      = (bool) $raw;
    }

    /**
     * Converts the cookie to a printable string.
     *
     * @return string
     */
    public function __toString()
    {
        $name  = $this->raw ? $this->name : rawurlencode($this->name);
        $parts = [];

        if ($this->value) {
            $parts[] = $this->raw ? $this->value : rawurlencode($this->value);
            if (0 !== $this->expire) {
                $parts[] = 'expires='.gmdate('D, d-M-Y H:i:s T', $this->expire);
            }
        } else {
            $parts[] = 'deleted; expires=Thu, 01-Jan-1970 00:00:00 GMT';
        }

        if ($this->path) {
            $parts[] = 'path='.$this->path;
        }

        if ($this->domain) {
            $parts[] = 'domain='.$this->domain;
        }

        if ($this->secure) {
            $parts[] = 'secure';
        }

        if ($this->httpOnly) {
            $parts[] = 'httponly';
        }

        return $name.'='.implode('; ', $parts);
    }

    /**
     * Sets the cookie name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Gets the cookie name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the cookie value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = (string) $value;
    }

    /**
     * Gets the cookie value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the cookie expire time.
     *
     * @param int $expire
     */
    public function setExpire($expire)
    {
        $this->expire = (int) $expire;
    }

    /**
     * Gets the cookie expire time.
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Sets the cookie path.
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = (string) $path;
    }

    /**
     * Gets the cookie path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the cookie domain.
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = (string) $domain;
    }

    /**
     * Gets the cookie domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets whether the cookie is for HTTPS only.
     *
     * When set to true, the cookie should only be transmitted over a secure
     * connection.
     *
     * @param bool $secure
     */
    public function setSecure($secure)
    {
        $this->secure = (bool) $secure;
    }

    /**
     * Checks whether the cookie is for HTTPS only.
     *
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Sets whether the cookie is meant for HTTP only.
     *
     * When set to true, scripting languages won't have access to the cookie.
     * This is a method to help reduce XSS identity theft.
     *
     * Note: May not be supported by all browsers.
     *
     * @param bool $httpOnly
     */
    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = (bool) $httpOnly;
    }

    /**
     * Checks whether the cookie is meant for HTTP only.
     *
     * @return bool
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Sets whether the cookie should be written raw.
     *
     * @param bool $raw
     */
    public function setRaw($raw)
    {
        $this->raw = (bool) $raw;
    }

    /**
     * Checks whether the cookie should be written raw.
     *
     * @return bool
     */
    public function isRaw()
    {
        return $this->raw;
    }
}
