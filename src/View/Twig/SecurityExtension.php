<?php

namespace Bitty\View\Twig;

use Bitty\Security\Context\ContextMapInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class SecurityExtension extends Twig_Extension
{
    /**
     * @var ContextMapInterface
     */
    protected $securityContext = null;

    /**
     * @var ServerRequestInterface
     */
    protected $request = null;

    /**
     * @param ContextMapInterface $securityContext
     * @param ServerRequestInterface $request
     */
    public function __construct(ContextMapInterface $securityContext, ServerRequestInterface $request)
    {
        $this->securityContext = $securityContext;
        $this->request         = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('is_granted', [$this, 'isGranted']),
        ];
    }

    /**
     * Checks if the current user has access to the given role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function isGranted($role)
    {
        $user = $this->securityContext->getUser($this->request);
        if (!$user) {
            return false;
        }

        $roles = $user->getRoles();

        // TODO: Need to account for authorization (i.e. voters)

        return in_array($role, $roles);
    }
}