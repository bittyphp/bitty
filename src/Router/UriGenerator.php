<?php

namespace Bitty\Router;

use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\UriGeneratorInterface;

class UriGenerator implements UriGeneratorInterface
{
    /**
     * @var RouteCollectionInterface
     */
    protected $routes = null;

    /**
     * @var string
     */
    protected $domain = null;

    /**
     * @param RouteCollectionInterface $routes
     * @param string $domain
     */
    public function __construct(RouteCollectionInterface $routes, $domain = '')
    {
        $this->routes = $routes;
        $this->domain = rtrim($domain, '/');
    }

    /**
     * {@inheritDoc}
     */
    public function generate($name, array $params = [], $type = self::ABSOLUTE_PATH)
    {
        $route = $this->routes->get($name);

        // TODO: This should take extra params and add them as a query string
        // TODO: Probably should blow up if missing required params

        $path = $route->getPath();
        foreach ($params as $id => $value) {
            $path = str_replace('{'.$id.'}', (string) $value, $path);
        }

        if (self::ABSOLUTE_URI === $type) {
            return $this->domain.'/'.ltrim($path, '/');
        }

        return $path;
    }
}
