<?php

namespace Bitty\View\Twig;

use Bitty\Router\UriGeneratorInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class UriGeneratorExtension extends Twig_Extension
{
    /**
     * @var UriGeneratorInterface
     */
    protected $uriGenerator = null;

    /**
     * @param UriGeneratorInterface $uriGenerator
     */
    public function __construct(UriGeneratorInterface $uriGenerator)
    {
        $this->uriGenerator = $uriGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('path', [$this, 'path']),
            new Twig_SimpleFunction('absolute_uri', [$this, 'absoluteUri']),
        ];
    }

    /**
     * Generates a absolute path for the given route.
     *
     * @param string $name
     * @param mixed[] $params
     *
     * @return string
     */
    public function path($name, array $params = [])
    {
        return $this->uriGenerator->generate($name, $params);
    }

    /**
     * Generates a absolute URI for the given route.
     *
     * @param string $name
     * @param mixed[] $params
     *
     * @return string
     */
    public function absoluteUri($name, array $params = [])
    {
        return $this->uriGenerator->generate($name, $params, UriGeneratorInterface::ABSOLUTE_URI);
    }
}
