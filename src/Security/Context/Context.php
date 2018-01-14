<?php

namespace Bitty\Security\Context;

use Bitty\Security\Context\ContextInterface;
use Psr\Http\Message\ServerRequestInterface;

class Context implements ContextInterface
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string[]
     */
    protected $paths = null;

    /**
     * @var bool
     */
    protected $default = null;

    /**
     * @var int
     */
    protected $ttl = null;

    /**
     * @var int
     */
    protected $delay = null;

    /**
     * @param string $name
     * @param string[] $paths
     * @param bool $default Whether or not this is the default security context.
     * @param int $ttl Time-to-Live; how long (in seconds) authentication lasts.
     * @param int $delay Delay (in seconds) before destroy on re-authentication.
     */
    public function __construct($name, array $paths, $default = true, $ttl = 86400, $delay = 300)
    {
        $this->name    = $name;
        $this->paths   = $paths;
        $this->default = (bool) $default;
        $this->ttl     = (int) $ttl;
        $this->delay   = (int) $delay;
    }

    /**
     * {@inheritDoc}
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value)
    {
        if ('user' === $name) {
            // TODO: Secure this more?
            // http://php.net/manual/en/features.session.security.management.php#features.session.security.management.session-id-regeneration
            // http://php.net/manual/en/function.session-regenerate-id.php
            $this->set('destroyed', time());
            session_regenerate_id();
            $this->remove('destroyed');
            $this->set('expires', time() + $this->ttl);
        }

        $_SESSION['shield.'.$this->name][$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $default = null)
    {
        if ('user' === $name) {
            $expires = $this->get('expires');
            if ($expires && time() > $expires) {
                // This session has timed out.
                // Clear out all data to prevent unauthorized use.
                $this->clear();
            }

            $destroyed = $this->get('destroyed', null);
            if ($destroyed && time() > $destroyed + $this->delay) {
                // This session has been destroyed.
                // Clear out all data to prevent unauthorized use.
                // TODO: Trigger alarm/log/event?
                $this->clear();
            }
        }

        if (isset($_SESSION['shield.'.$this->name][$name])) {
            return $_SESSION['shield.'.$this->name][$name];
        }

        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($name)
    {
        if (isset($_SESSION['shield.'.$this->name][$name])) {
            unset($_SESSION['shield.'.$this->name][$name]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $_SESSION['shield.'.$this->name] = [];
    }

    /**
     * {@inheritDoc}
     */
    public function isShielded(ServerRequestInterface $request)
    {
        $match = $this->getPatternMatch($request);

        return !empty($match) && !empty($match['roles']);
    }

    /**
     * {@inheritDoc}
     */
    public function getPatternMatch(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        foreach ($this->paths as $pattern => $roles) {
            if (preg_match("`$pattern`", $path)) {
                return [
                    'shield' => $this->name,
                    'pattern' => $pattern,
                    'roles' => $roles,
                ];
            }
        }

        return [];
    }
}
