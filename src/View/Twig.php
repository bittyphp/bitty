<?php

namespace Bitty\View;

use Bitty\Http\Response;
use Bitty\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Twig_Environment;
use Twig_ExtensionInterface;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * This acts as a very basic wrapper to implement the Twig templating engine.
 *
 * If more detailed customization is needed, you can access the Twig environment
 * and the loader directly using getEnvironment() and getLoader(), respectively.
 *
 * @see https://twig.symfony.com/
 */
class Twig implements ViewInterface
{
    /**
     * @var Twig_Loader_Filesystem
     */
    protected $loader = null;

    /**
     * @var Twig_Environment
     */
    protected $environment = null;

    /**
     * @param string[]|string $paths
     * @param mixed[] $options
     */
    public function __construct($paths, array $options = [])
    {
        $this->loader = new Twig_Loader_Filesystem();
        foreach ((array) $paths as $namespace => $path) {
            if (is_string($namespace)) {
                $this->loader->addPath($path, $namespace);
            } else {
                $this->loader->addPath($path);
            }
        }

        $this->environment = new Twig_Environment($this->loader, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function render($template, array $data = [])
    {
        return $this->environment->load($template)->render($data);
    }

    /**
     * Renders a single block from a template using the given context data.
     *
     * @param string $template Template to render.
     * @param string $block Name of block in the template.
     * @param array $data Data to pass to template.
     *
     * @return string
     */
    public function renderBlock($template, $block, array $data = [])
    {
        return $this->environment->load($template)->renderBlock($block, $data);
    }

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

    /**
     * Adds a Twig extension.
     *
     * @param Twig_ExtensionInterface $extension
     */
    public function addExtension(Twig_ExtensionInterface $extension)
    {
        $this->environment->addExtension($extension);
    }

    /**
     * Adds a Twig filter.
     *
     * @param string $name
     * @param Twig_SimpleFilter $filter
     */
    public function addFilter($name, Twig_SimpleFilter $filter)
    {
        $this->environment->addFilter($name, $filter);
    }

    /**
     * Adds a Twig function.
     *
     * @param string $name
     * @param Twig_SimpleFunction $function
     */
    public function addFunction($name, Twig_SimpleFunction $function)
    {
        $this->environment->addFunction($name, $function);
    }

    /**
     * Gets the Twig loader.
     *
     * This allows for direct manipulation of anything not already defined here.
     *
     * @return Twig_Loader_Filesystem
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Gets the Twig environment.
     *
     * This allows for direct manipulation of anything not already defined here.
     *
     * @return Twig_Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
