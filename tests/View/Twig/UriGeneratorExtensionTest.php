<?php

namespace Bitty\Tests\View\Twig;

use Bitty\Router\UriGeneratorInterface;
use Bitty\Tests\TestCase;
use Bitty\View\Twig\UriGeneratorExtension;
use Twig_ExtensionInterface;
use Twig_SimpleFunction;

class UriGeneratorExtensionTest extends TestCase
{
    /**
     * @var UriGeneratorExtension
     */
    protected $fixture = null;

    /**
     * @var UriGeneratorInterface
     */
    protected $uriGenerator = null;

    protected function setUp()
    {
        parent::setUp();

        $this->uriGenerator = $this->createMock(UriGeneratorInterface::class);

        $this->fixture = new UriGeneratorExtension($this->uriGenerator);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(Twig_ExtensionInterface::class, $this->fixture);
    }

    public function testGetFunctions()
    {
        $actual = $this->fixture->getFunctions();

        $this->assertContainsOnlyInstancesOf(Twig_SimpleFunction::class, $actual);
        $this->assertCount(2, $actual);
        $this->assertEquals('path', $actual[0]->getName());
        $this->assertEquals([$this->fixture, 'path'], $actual[0]->getCallable());
        $this->assertEquals('absolute_uri', $actual[1]->getName());
        $this->assertEquals([$this->fixture, 'absoluteUri'], $actual[1]->getCallable());
    }

    public function testPath()
    {
        $name   = uniqid('name');
        $params = [uniqid('param')];
        $uri    = uniqid('uri');

        $this->uriGenerator->expects($this->once())
            ->method('generate')
            ->with($name, $params, UriGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn($uri);

        $actual = $this->fixture->path($name, $params);

        $this->assertEquals($uri, $actual);
    }

    public function testAbsoluteUri()
    {
        $name   = uniqid('name');
        $params = [uniqid('param')];
        $uri    = uniqid('uri');

        $this->uriGenerator->expects($this->once())
            ->method('generate')
            ->with($name, $params, UriGeneratorInterface::ABSOLUTE_URI)
            ->willReturn($uri);

        $actual = $this->fixture->absoluteUri($name, $params);

        $this->assertEquals($uri, $actual);
    }
}
