<?php

namespace Bitty\Tests\View;

use Bitty\Tests\TestCase;
use Bitty\View\Twig;
use Bitty\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_ExtensionInterface;

class TwigTest extends TestCase
{
    /**
     * @var Twig
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Twig(
            [
                __DIR__.'/templates/',
                'test' => __DIR__.'/templates/parent/',
            ]
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ViewInterface::class, $this->fixture);
    }

    /**
     * @dataProvider sampleRender
     */
    public function testRender($template, $data, $expected)
    {
        $actual = $this->fixture->render($template, $data);

        $this->assertEquals($expected, $actual);
    }

    public function sampleRender()
    {
        $name = uniqid('name');

        return [
            'simple' => [
                'template' => 'test.html.twig',
                'data' => ['name' => $name],
                'expected' => 'Hello, '.$name.PHP_EOL.PHP_EOL.'Goodbye, '.$name.PHP_EOL,
            ],
            'nested' => [
                'template' => 'parent/test.html.twig',
                'data' => ['name' => $name],
                'expected' => 'Hello, '.$name.', from parent'.PHP_EOL,
            ],
            'multiple nested' => [
                'template' => 'parent/child/test.html.twig',
                'data' => ['name' => $name],
                'expected' => 'Hello, '.$name.', from parent/child'.PHP_EOL,
            ],
            'namespaced' => [
                'template' => '@test/test.html.twig',
                'data' => ['name' => $name],
                'expected' => 'Hello, '.$name.', from parent'.PHP_EOL,
            ],
        ];
    }

    public function testRenderBlock()
    {
        $name = uniqid('name');

        $actual = $this->fixture->renderBlock('test.html.twig', 'hello', ['name' => $name]);

        $this->assertEquals('Hello, '.$name.PHP_EOL, $actual);
    }

    /**
     * @dataProvider sampleRender
     */
    public function testRenderResponse($template, $data, $expected)
    {
        $actual = $this->fixture->renderResponse($template, $data);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals($expected, (string) $actual->getBody());
    }

    public function testAddExtension()
    {
        $name      = uniqid('name');
        $extension = $this->createConfiguredMock(Twig_ExtensionInterface::class, ['getName' => $name]);

        $this->fixture->addExtension($extension);

        $actual = $this->fixture->getEnvironment()->getExtensions();

        $this->assertSame($extension, $actual[$name]);
    }

    public function testGetLoader()
    {
        $actual = $this->fixture->getLoader();

        $this->assertInstanceOf(Twig_Loader_Filesystem::class, $actual);
    }

    public function testGetEnvironment()
    {
        $actual = $this->fixture->getEnvironment();

        $this->assertInstanceOf(Twig_Environment::class, $actual);
    }
}
