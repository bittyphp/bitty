<?php

namespace Bitty\Tests\Router;

use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Router\UriGenerator;
use Bitty\Router\UriGeneratorInterface;
use Bitty\Tests\TestCase;

class UriGeneratorTest extends TestCase
{
    /**
     * @var UriGenerator
     */
    protected $fixture = null;

    /**
     * @var RouteCollectionInterface
     */
    protected $routes = null;

    protected function setUp()
    {
        parent::setUp();

        $this->routes = $this->createMock(RouteCollectionInterface::class);

        $this->fixture = new UriGenerator($this->routes);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(UriGeneratorInterface::class, $this->fixture);
    }

    public function testGenerateGetsRoute()
    {
        $name  = uniqid();
        $route = $this->createMock(RouteInterface::class);

        $this->routes->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($route);

        $this->fixture->generate($name);
    }

    /**
     * @dataProvider sampleGenerate
     */
    public function testGenerate($path, $domain, $name, $params, $type, $expected)
    {
        $route = $this->createConfiguredMock(RouteInterface::class, ['getPath' => $path]);
        $this->routes->method('get')->willReturn($route);

        $fixture = new UriGenerator($this->routes, $domain);
        $actual  = $fixture->generate($name, $params, $type);

        $this->assertEquals($expected, $actual);
    }

    public function sampleGenerate()
    {
        $name   = uniqid('name');
        $pathA  = '/'.uniqid('path');
        $pathB  = uniqid('path');
        $paramA = uniqid('param');
        $paramB = uniqid('param');
        $domain = uniqid('domain');

        return [
            'no params' => [
                'path' => $pathA,
                'domain' => '',
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA,
            ],
            'one param' => [
                'path' => $pathA.'/{paramA}',
                'domain' => '',
                'name' => $name,
                'params' => ['paramA' => $paramA],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA.'/'.$paramA,
            ],
            'multiple params' => [
                'path' => $pathA.'/{paramA}/{paramB}',
                'domain' => '',
                'name' => $name,
                'params' => ['paramA' => $paramA, 'paramB' => $paramB],
                'type' => UriGeneratorInterface::ABSOLUTE_PATH,
                'expected' => $pathA.'/'.$paramA.'/'.$paramB,
            ],
            'domain with trailing slash' => [
                'path' => $pathB,
                'domain' => $domain.'/',
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_URI,
                'expected' => $domain.'/'.$pathB,
            ],
            'path with leading slash' => [
                'path' => $pathA,
                'domain' => $domain,
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_URI,
                'expected' => $domain.$pathA,
            ],
            'no slashes' => [
                'path' => $pathB,
                'domain' => $domain,
                'name' => $name,
                'params' => [],
                'type' => UriGeneratorInterface::ABSOLUTE_URI,
                'expected' => $domain.'/'.$pathB,
            ],
        ];
    }
}
