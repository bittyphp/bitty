<?php

namespace Bizurkur\Bitty\Security;

use Bizurkur\Bitty\Collection;
use Bizurkur\Bitty\Http\RedirectResponse;
use Bizurkur\Bitty\Http\Response;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Security\AuthenticationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
            'logout.path' => '/logout',
            'logout.target' => '/',
            'login.username' => 'username',
            'login.password' => 'password',
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

        $userField = $this->config->get('login.username');
        $passField = $this->config->get('login.password');

        $user = empty($params[$userField]) ? null : $params[$userField];
        $pass = empty($params[$passField]) ? null : $params[$passField];

        if ($this->authentication->authenticate($user, $pass)) {
            // TODO: Redirect to referrer
            $target = $this->config->get('login.target');

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
}
