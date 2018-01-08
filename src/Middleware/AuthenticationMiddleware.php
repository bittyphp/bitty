<?php

namespace Bizurkur\Bitty\Middleware;

use Bizurkur\Bitty\Collection;
use Bizurkur\Bitty\Http\RedirectResponse;
use Bizurkur\Bitty\Http\Response;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Security\Authentication\ProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @var ProviderInterface
     */
    protected $authProvider = null;

    /**
     * @var Collection
     */
    protected $config = null;

    /**
     * @param ProviderInterface $authProvider
     * @param mixed[] $config
     */
    public function __construct(ProviderInterface $authProvider, array $config = [])
    {
        $config += [
            'paths' => [],
            'type' => 'basic',
            'basic.realm' => 'Secured Area',
            'form.login' => '/login',
            'form.logout' => '/logout',
            'form.target' => '/',
            'form.username' => 'username',
            'form.password' => 'password',
        ];

        $this->authProvider = $authProvider;
        $this->config       = new Collection($config);
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

    protected function getAuthenticationResponse(ServerRequestInterface $request)
    {
        $type = $this->config->get('type');

        if ('form' === $type) {
            $loginUri  = $this->config->get('form.login');
            $logoutUri = $this->config->get('form.logout');
            if ($loginUri === $request->getUri()->getPath()) {
                return $this->getFormLogin($request);
            }

            if ($logoutUri === $request->getUri()->getPath()) {
                $this->authProvider->deauthenticate();

                return new RedirectResponse($this->config->get('form.target'));
            }
        }

        $paths = $this->config->get('paths', []);
        if (empty($paths)) {
            return false;
        }

        $pattern = implode('|', $paths);
        $path    = $request->getUri()->getPath();

        if (!preg_match("`^(?:$pattern)$`", $path)) {
            return false;
        }

        if ($this->authProvider->isAuthenticated()) {
            return false;
        }

        if ('basic' === $type) {
            return $this->getBasicLogin($request);
        }

        return $this->getFormLogin($request);
    }

    protected function getBasicLogin(ServerRequestInterface $request)
    {
        $params = $request->getServerParams();
        $user   = empty($params['PHP_AUTH_USER']) ? null : $params['PHP_AUTH_USER'];
        $pass   = empty($params['PHP_AUTH_PW']) ? null : $params['PHP_AUTH_PW'];

        if ($user && $pass && $this->authProvider->authenticate($user, $pass)) {
            return false;
        }

        $headers = [
            'WWW-Authenticate' => sprintf(
                'Basic realm="%s"',
                $this->config->get('basic.realm')
            ),
        ];

        return new Response('', 401, $headers);
    }

    protected function getFormLogin(ServerRequestInterface $request)
    {
        $loginUri = $this->config->get('form.login');
        $response = new RedirectResponse($loginUri);

        if ($loginUri !== $request->getUri()->getPath()) {
            return $response;
        }

        if ('GET' === $request->getMethod()) {
            return false;
        }

        $params = $request->getParsedBody();
        if (empty($params)) {
            return false;
        }

        $userField = $this->config->get('form.username');
        $passField = $this->config->get('form.password');

        $user = empty($params[$userField]) ? null : $params[$userField];
        $pass = empty($params[$passField]) ? null : $params[$passField];

        if ($user && $pass && $this->authProvider->authenticate($user, $pass)) {
            // TODO: Redirect to referrer
            $target = $this->config->get('form.target');

            return new RedirectResponse($target);
        }

        return $response;
    }
}
