<?php

namespace Bitty\View;

use Bitty\Http\Response;
use Bitty\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractView implements ViewInterface
{
    /**
     * {@inheritDoc}
     */
    abstract public function render($template, array $data = []);

    /**
     * Renders an HTTP response using the template and given data.
     *
     * @param string $template Template to render.
     * @param array $data Data to pass to template.
     *
     * @return ResponseInterface
     */
    public function renderResponse($template, array $data = [])
    {
        return new Response($this->render($template, $data));
    }
}
