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
     * @var mixed[]
     */
    protected $config = null;

    /**
     * @param string $name
     * @param string[] $paths
     * @param mixed[] $config
     */
    public function __construct($name, array $paths, array $config = [])
    {
        $this->name   = $name;
        $this->paths  = $paths;
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * {@inheritDoc}
     */
    public function isDefault()
    {
        return (bool) $this->config['default'];
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value)
    {
        if ('user' === $name) {
            $now = time();
            // TODO: Secure this more?
            // http://php.net/manual/en/features.session.security.management.php#features.session.security.management.session-id-regeneration
            // http://php.net/manual/en/function.session-regenerate-id.php
            $this->set('destroy', $now + $this->config['destroy.delay']);
            session_regenerate_id();
            $this->remove('destroy');
            $this->set('login', $now);
            $this->set('active', $now);
            $this->set('expires', $now + $this->config['ttl']);
        }

        $_SESSION['shield.'.$this->name][$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $default = null)
    {
        if ('user' === $name) {
            $now     = time();
            $expires = $this->get('expires', 0);
            $destroy = $this->get('destroy', INF);
            $active  = $this->get('active', 0) + ($this->config['timeout'] ?: INF);
            $clear   = min($expires, $destroy, $active);

            if ($now > $clear) {
                // This session should be destroyed.
                // Clear out all data to prevent unauthorized use.
                $this->clear();
            } else {
                // Update last active time.
                $this->set('active', $now);
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

    /**
     * Gets the default configuration settings.
     *
     * @return mixed[]
     */
    protected function getDefaultConfig()
    {
        return [
            'default' => true,
            'ttl' => 86400,
            'timeout' => 0,
            'destroy.delay' => 300,
        ];
    }
}
