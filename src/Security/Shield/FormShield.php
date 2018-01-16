<?php

namespace Bitty\Security\Shield;

use Bitty\Http\RedirectResponse;
use Bitty\Http\Response;
use Bitty\Security\Shield\AbstractShield;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormShield extends AbstractShield
{
    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();

        if ($path === $this->config['login.path']) {
            return $this->handleFormLogin($request);
        }

        if ($path === $this->config['logout.path']) {
            $this->context->clear();

            return new RedirectResponse($this->config['logout.target']);
        }

        $match = $this->context->getPatternMatch($request);
        if (empty($match) || empty($match['roles'])) {
            return;
        }

        $user = $this->context->get('user');
        if (!$user) {
            $this->context->set('login.target', $path);

            return new RedirectResponse($this->config['login.path']);
        }

        if (!$this->authorizer->authorize($user, $match['roles'])) {
            return new Response('', 403);
        }
    }

    /**
     * Handles form logins.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface|null
     */
    protected function handleFormLogin(ServerRequestInterface $request)
    {
        if ('POST' !== $request->getMethod()) {
            return;
        }

        $usernameField = $this->config['login.username'];
        $passwordField = $this->config['login.password'];
        $rememberField = $this->config['login.remember'];

        $params = $request->getParsedBody();
        if (!is_array($params)) {
            return;
        }

        $username = empty($params[$usernameField]) ? '' : $params[$usernameField];
        $password = empty($params[$passwordField]) ? '' : $params[$passwordField];
        $remember = empty($params[$rememberField]) ? false : true;

        if (empty($username) || empty($password)) {
            return;
        }

        $user = $this->authenticator->authenticate($username, $password);
        $this->context->set('user', $user);

        $target = $this->config['login.target'];
        if ($this->config['login.use_referrer']) {
            $target = $this->context->get('login.target', $target);
            $this->context->remove('login.target');
        }

        return new RedirectResponse($target);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'login.path' => '/login',
            'login.target' => '/',
            'login.username' => 'username',
            'login.password' => 'password',
            'login.remember' => 'remember',
            'login.use_referrer' => true,
            'logout.path' => '/logout',
            'logout.target' => '/',
        ];
    }
}
