<?php

namespace Bitty\View;

interface ViewInterface
{
    /**
     * Renders a template using the given data.
     *
     * @param string $template Template to render.
     * @param mixed $data Data to pass to template.
     *
     * @return string
     */
    public function render($template, $data = []);
}
