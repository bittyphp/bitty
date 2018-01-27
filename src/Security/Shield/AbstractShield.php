<?php

namespace Bitty\Security\Shield;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerAwareTrait;
use Bitty\EventManager\EventInterface;
use Bitty\Security\Authentication\AuthenticatorInterface;
use Bitty\Security\Authorization\AuthorizerInterface;
use Bitty\Security\Context\ContextInterface;
use Bitty\Security\Exception\AuthenticationException;
use Bitty\Security\Exception\AuthorizationException;
use Bitty\Security\Shield\ShieldInterface;
use Bitty\Security\User\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractShield implements ShieldInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ContextInterface
     */
    protected $context = null;

    /**
     * @var AuthenticatorInterface
     */
    protected $authenticator = null;

    /**
     * @var AuthorizerInterface
     */
    protected $authorizer = null;

    /**
     * @var mixed[]
     */
    protected $config = null;

    /**
     * @param ContextInterface $context
     * @param AuthenticatorInterface $authenticator
     * @param AuthorizerInterface $authorizer
     * @param mixed[] $config
     */
    public function __construct(
        ContextInterface $context,
        AuthenticatorInterface $authenticator,
        AuthorizerInterface $authorizer,
        array $config = []
    ) {
        $this->context       = $context;
        $this->authenticator = $authenticator;
        $this->authorizer    = $authorizer;
        $this->config        = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function handle(ServerRequestInterface $request);

    /**
     * {@inheritDoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Gets the default config settings for the shield.
     *
     * @return mixed[]
     */
    protected function getDefaultConfig()
    {
        return [];
    }

    /**
     * Triggers security events to enable external actions, e.g. logging.
     *
     * @param string|EventInterface $event
     * @param null|object|string $target
     * @param mixed[] $params
     *
     * @return mixed
     */
    protected function triggerEvent($event, $target = null, $params = [])
    {
        if (!$this->container || !$this->container->has('event.manager')) {
            return;
        }

        $eventManager = $this->container->get('event.manager');

        return $eventManager->trigger($event, $target, $params);
    }

    /**
     * Authenticates a user.
     *
     * @param string $username
     * @param string $password
     *
     * @return UserInterface
     *
     * @throws AuthenticationException
     */
    protected function authenticate($username, $password)
    {
        $this->triggerEvent('security.authentication.start', null, ['username' => $username]);
        try {
            $user = $this->authenticator->authenticate($username, $password);
        } catch (AuthenticationException $exception) {
            $this->triggerEvent(
                'security.authentication.failure',
                null,
                [
                    'username' => $username,
                    'error' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
        $this->context->set('user', $user);
        $this->triggerEvent('security.authentication.success', $user);

        return $user;
    }

    /**
     * Authorizes a user.
     *
     * @param UserInterface $user
     * @param string[] $roles
     *
     * @throws AuthorizationException
     */
    protected function authorize(UserInterface $user, array $roles)
    {
        $this->triggerEvent('security.authorization.start', $user);
        try {
            $this->authorizer->authorize($user, $roles);
        } catch (AuthorizationException $exception) {
            $this->triggerEvent(
                'security.authorization.failure',
                $user,
                ['error' => $exception->getMessage()]
            );

            throw $exception;
        }
        $this->triggerEvent('security.authorization.success', $user);
    }
}
