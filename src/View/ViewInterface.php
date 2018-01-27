<?php

namespace Bitty\View;

interface ViewInterface
{
    /**
     * Renders a template using the given data.
     *
     * @param string $template Template to render.
     * @param array $data Data to pass to template.
     *
     * @return string
     */
    public function render($template, array $data = []);
}
