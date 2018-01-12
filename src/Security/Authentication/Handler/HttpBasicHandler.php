<?php

namespace Bizurkur\Bitty\Security\Authentication\Handler;

use Bizurkur\Bitty\Http\Response;
use Bizurkur\Bitty\Security\Authentication\Handler\AbstractHandler;
use Psr\Http\Message\ServerRequestInterface;

class HttpBasicHandler extends AbstractHandler
{
    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request)
    {
        if (!$this->context->isSecuredPath($request)) {
            return;
        }

        if ($this->authenticator->isAuthenticated()) {
            return;
        }

        $params   = $request->getServerParams();
        $username = empty($params['PHP_AUTH_USER']) ? null : $params['PHP_AUTH_USER'];
        $password = empty($params['PHP_AUTH_PW']) ? null : $params['PHP_AUTH_PW'];

        if ($this->authenticator->authenticate($username, $password)) {
            return;
        }

        $headers = [
            'WWW-Authenticate' => sprintf(
                'Basic realm="%s"',
                $this->context->getRealm()
            ),
        ];

        return new Response('', 401, $headers);
    }
}
