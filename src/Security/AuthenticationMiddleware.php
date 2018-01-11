<?php

namespace Bizurkur\Bitty\Security;

use Bizurkur\Bitty\Collection;
use Bizurkur\Bitty\Http\RedirectResponse;
use Bizurkur\Bitty\Http\Response;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Http\Uri;
use Bizurkur\Bitty\Security\AuthenticationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Middleware to handle both HTTP Basic and username/password form authentication.
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthenticationInterface
     */
    protected $authentication = null;

    /**
     * @var Collection
     */
    protected $config = null;

    /**
     * @param AuthenticationInterface $authentication
     * @param mixed[] $config
     */
    public function __construct(AuthenticationInterface $authentication, array $config = [])
    {
        $config += [
            'paths' => [],
            'type' => 'basic',
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

        $this->authentication = $authentication;
        $this->config         = new Collection($config);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $response = $this->getAuthenticationResponse($request);
        if ($response) {
            return $response;
        }

        return $handler->handle($request);
    }

    /**
     * Gets an authentication response, if applicable.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface|null
     */
    protected function getAuthenticationResponse(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();

        if ($this->isFormPath('login', $path)) {
            return $this->handleForm($request);
        }

        if ($this->isFormPath('logout', $path)) {
            $this->authentication->deauthenticate();

            return new RedirectResponse($this->config->get('logout.target'));
        }

        if (!$this->isSecuredPath($path)) {
            return;
        }

        if ($this->authentication->isAuthenticated()) {
            return;
        }

        if ($this->config->get('type') === 'basic') {
            return $this->handleHttpBasic($request);
        }

        $_SESSION['last_referrer'] = $path;

        return new RedirectResponse($this->config->get('login.path'));
    }

    /**
     * Checks if the path matches a form path.
     *
     * @param string $name
     * @param string $path
     *
     * @return bool
     */
    protected function isFormPath($name, $path)
    {
        if ($this->config->get('type') !== 'form') {
            return false;
        }

        return $this->config->get($name.'.path') === $path;
    }

    /**
     * Handles form logins.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface|null
     */
    protected function handleForm(ServerRequestInterface $request)
    {
        if ('GET' === $request->getMethod()) {
            return;
        }

        $params = $request->getParsedBody();
        if (empty($params)) {
            return;
        }

        $usernameField = $this->config->get('login.username');
        $passwordField = $this->config->get('login.password');
        $rememberField = $this->config->get('login.remember');

        $username = empty($params[$usernameField]) ? null : $params[$usernameField];
        $password = empty($params[$passwordField]) ? null : $params[$passwordField];
        $remember = empty($params[$rememberField]) ? false : true;

        if ($this->authentication->authenticate($username, $password, $remember)) {
            $target = $this->getLoginTarget($request);

            return new RedirectResponse($target);
        }
    }

    /**
     * Checks if the path has been secured.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function isSecuredPath($path)
    {
        $paths = $this->config->get('paths', []);
        if (empty($paths)) {
            return false;
        }

        foreach ($paths as $pattern) {
            if (preg_match("`$pattern`", $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handles an HTTP Basic authentication.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface|null
     */
    protected function handleHttpBasic(ServerRequestInterface $request)
    {
        $params = $request->getServerParams();
        $user   = empty($params['PHP_AUTH_USER']) ? null : $params['PHP_AUTH_USER'];
        $pass   = empty($params['PHP_AUTH_PW']) ? null : $params['PHP_AUTH_PW'];

        if ($this->authentication->authenticate($user, $pass)) {
            return;
        }

        $headers = [
            'WWW-Authenticate' => sprintf(
                'Basic realm="%s"',
                $this->config->get('realm')
            ),
        ];

        return new Response('', 401, $headers);
    }

    /**
     * Gets the target URI to go to after login success.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function getLoginTarget(ServerRequestInterface $request)
    {
        $target = $this->config->get('login.target');
        if (!$this->config->get('login.use_referrer')) {
            return $target;
        }

        $referrer = $this->getReferrer($request);
        if (!$referrer) {
            return $target;
        }

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
     * Gets the referrer, if one is set.
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    protected function getReferrer(ServerRequestInterface $request)
    {
        if (!empty($_SESSION['last_referrer'])) {
            $referrer = $_SESSION['last_referrer'];
            unset($_SESSION['last_referrer']);

            return $referrer;
        }

        $server = $request->getServerParams();
        if (isset($server['HTTP_REFERER'])) {
            return $server['HTTP_REFERER'];
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
}
