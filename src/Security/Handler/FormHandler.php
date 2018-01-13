<?php

namespace Bitty\Security\Handler;

use Bitty\Http\RedirectResponse;
use Bitty\Http\Response;
use Bitty\Security\Handler\AbstractHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormHandler extends AbstractHandler
{
    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        if ($this->context->isLoginPath($request)) {
            return $this->handleFormLogin($request);
        }

        if ($this->context->isLogoutPath($request)) {
            $this->authenticator->deauthenticate();

            return new RedirectResponse($this->context->getLogoutTarget());
        }

        if (!$this->context->isSecuredPath($request)) {
            return;
        }

        if ($this->authenticator->isAuthenticated()) {
            return;
        }

        $this->context->setReferrer($request);

        return new RedirectResponse($this->context->getLoginPath());
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
        if ('GET' === $request->getMethod()) {
            return;
        }

        $username = $this->context->getLoginUsername($request);
        $password = $this->context->getLoginPassword($request);
        $remember = $this->context->getLoginRemember($request);

        if ($this->authenticator->authenticate($username, $password, $remember)) {
            $target = $this->context->getLoginTarget($request);

            return new RedirectResponse($target);
        }
    }
}
