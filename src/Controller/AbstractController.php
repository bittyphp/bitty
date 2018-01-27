<?php

namespace Bitty\Controller;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerAwareTrait;
use Bitty\Http\Exception\InternalServerErrorException;
use Bitty\Http\RedirectResponse;
use Bitty\Http\Response;
use Bitty\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Redirects to a different route with the given parameters.
     *
     * @param string $name
     * @param mixed[] $params
     *
     * @return ResponseInterface
     */
    protected function redirectToRoute($name, array $params = [])
    {
        $uri = $this->container->get('uri.generator')->generate($name, $params);

        return new RedirectResponse($uri);
    }

    /**
     * Renders an HTTP response using the template and given data.
     *
     * @param string $template Template to render.
     * @param array $data Data to pass to template.
     *
     * @return ResponseInterface
     */
    protected function render($template, array $data = [])
    {
        $view = $this->container->get('view');
        if (!$view instanceof ViewInterface) {
            throw new InternalServerErrorException(
                sprintf(
                    'Container service "view" must be an instance of %s',
                    ViewInterface::class
                )
            );
        }

        $html = $view->render($template, $data);

        return new Response($html);
    }
}
