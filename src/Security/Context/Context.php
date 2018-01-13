<?php

namespace Bitty\Security\Context;

use Bitty\Collection;
use Bitty\Http\Uri;
use Bitty\Security\Context\ContextInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Context implements ContextInterface
{
    /**
     * @var Collection
     */
    protected $context = null;

    /**
     * @param mixed[] $context
     */
    public function __construct(array $context = [])
    {
        $context += $this->getDefaultContext();

        $this->context = new Collection($context);
    }

    /**
     * Gets the realm that's being secured.
     *
     * @return string
     */
    public function getRealm()
    {
        return $this->context->get('realm');
    }

    /**
     * Gets the username from the login form.
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    public function getLoginUsername(ServerRequestInterface $request)
    {
        $params    = $request->getParsedBody();
        $fieldName = $this->context->get('login.username');

        return empty($params[$fieldName]) ? null : $params[$fieldName];
    }

    /**
     * Gets the password from the login form.
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    public function getLoginPassword(ServerRequestInterface $request)
    {
        $params    = $request->getParsedBody();
        $fieldName = $this->context->get('login.password');

        return empty($params[$fieldName]) ? null : $params[$fieldName];
    }

    /**
     * Gets the remember me setting from the login form.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function getLoginRemember(ServerRequestInterface $request)
    {
        $params    = $request->getParsedBody();
        $fieldName = $this->context->get('login.remember');

        return empty($params[$fieldName]) ? false : true;
    }

    /**
     * Gets the path to the login form.
     *
     * @return string
     */
    public function getLoginPath()
    {
        return $this->context->get('login.path');
    }

    /**
     * Gets the target URI to go to after login success.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getLoginTarget(ServerRequestInterface $request)
    {
        $target = $this->context->get('login.target');
        if (!$this->context->get('login.use_referrer')) {
            return $target;
        }

        $referrer = $this->getReferrer($request);
        if (!$referrer) {
            return $target;
        }

        $this->clearReferrer();

        $referrerUri = new Uri($referrer);
        if (!$this->isValidReferrer($referrerUri, $request)) {
            return $target;
        }

        $target = $referrerUri->getPath();

        $query = $referrerUri->getQuery();
        if ($query) {
            $target .= '?'.$query;
        }

        return $target;
    }

    /**
     * Checks if the path matches the login path.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function isLoginPath(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();

        return $this->context->get('login.path') === $path;
    }

    /**
     * Gets the path to the logout form.
     *
     * @return string
     */
    public function getLogoutPath()
    {
        return $this->context->get('logout.path');
    }

    /**
     * Gets the target URI to go to after logout.
     *
     * @return string
     */
    public function getLogoutTarget()
    {
        return $this->context->get('logout.target');
    }

    /**
     * Checks if the path matches the logout path.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function isLogoutPath(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();

        return $this->context->get('logout.path') === $path;
    }

    /**
     * Checks if the path has been secured.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function isSecuredPath(ServerRequestInterface $request)
    {
        $paths = $this->context->get('paths', []);
        if (empty($paths)) {
            return false;
        }

        $path = $request->getUri()->getPath();

        foreach ($paths as $pattern) {
            if (preg_match("`$pattern`", $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the referrer to use during login redirect.
     *
     * @param ServerRequestInterface $request
     */
    public function setReferrer(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();

        $_SESSION['last_referrer'] = $path;
    }

    /**
     * Gets the referrer, if one is set.
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    public function getReferrer(ServerRequestInterface $request)
    {
        if (!empty($_SESSION['last_referrer'])) {
            return $_SESSION['last_referrer'];
        }

        $server = $request->getServerParams();
        if (isset($server['HTTP_REFERER'])) {
            return $server['HTTP_REFERER'];
        }
    }

    /**
     * Clears the referrer, if one is set.
     */
    public function clearReferrer()
    {
        if (isset($_SESSION['last_referrer'])) {
            unset($_SESSION['last_referrer']);
        }
    }

    /**
     * Checks if the referrer URI is valid.
     *
     * @param UriInterface $referrerUri
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isValidReferrer(UriInterface $referrerUri, ServerRequestInterface $request)
    {
        $requestUri = $request->getUri();
        if ($referrerUri->getPath() === $requestUri->getPath()) {
            return false;
        }

        if ('' === $referrerUri->getHost()) {
            return true;
        }

        if ($referrerUri->getHost() === $requestUri->getHost()) {
            return true;
        }

        return false;
    }

    /**
     * Gets the default context settings.
     *
     * @return mixed[]
     */
    protected function getDefaultContext()
    {
        return [
            'paths' => [],
            'realm' => 'Secured Area',
            'login.path' => '/login',
            'login.target' => '/',
            'login.use_referrer' => true,
            'logout.path' => '/logout',
            'logout.target' => '/',
            'login.username' => 'username',
            'login.password' => 'password',
            'login.remember' => 'remember',
        ];
    }
}
