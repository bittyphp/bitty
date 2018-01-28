<?php

namespace Bitty\Tests\Controller;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Controller\AbstractController;
use Bitty\Http\Exception\InternalServerErrorException;
use Bitty\Router\UriGeneratorInterface;
use Bitty\Tests\TestCase;
use Bitty\View\ViewInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class AbstractControllerTest extends TestCase
{
    /**
     * @var AbstractController
     */
    protected $fixture = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->fixture = $this->getMockForAbstractClass(AbstractController::class);
        $this->fixture->setContainer($this->container);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ContainerAwareInterface::class, $this->fixture);
    }

    public function testRedirectToRouteCallsContainer()
    {
        $uriGenerator = $this->createUriGenerator();

        $this->container->expects($this->once())
            ->method('get')
            ->with('uri.generator')
            ->willReturn($uriGenerator);

        $this->fixture->redirectToRoute(uniqid());
    }

    public function testRedirectToRouteCallsUriGenerator()
    {
        $name         = uniqid('name');
        $params       = [uniqid('param')];
        $uriGenerator = $this->createUriGenerator();

        $this->container->method('get')->willReturn($uriGenerator);

        $uriGenerator->expects($this->once())
            ->method('generate')
            ->with($name, $params);

        $this->fixture->redirectToRoute($name, $params);
    }

    public function testRedirectToRouteResponse()
    {
        $uri          = uniqid('uri');
        $uriGenerator = $this->createUriGenerator($uri);

        $this->container->method('get')->willReturn($uriGenerator);

        $actual = $this->fixture->redirectToRoute(uniqid());

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals([$uri], $actual->getHeader('Location'));
        $this->assertEquals(302, $actual->getStatusCode());
    }

    public function testRenderCallsContainer()
    {
        $view = $this->createView();

        $this->container->expects($this->once())
            ->method('get')
            ->with('view')
            ->willReturn($view);

        $this->fixture->render(uniqid());
    }

    public function testRenderThrowsException()
    {
        $message = 'Container service "view" must be an instance of '.ViewInterface::class;
        $this->setExpectedException(InternalServerErrorException::class, $message);

        $this->fixture->render(uniqid());
    }

    public function testRenderCallsView()
    {
        $template = uniqid('template');
        $data     = [uniqid('data')];
        $view     = $this->createView();

        $this->container->method('get')->willReturn($view);

        $view->expects($this->once())
            ->method('render')
            ->with($template, $data);

        $this->fixture->render($template, $data);
    }

    public function testRenderResponse()
    {
        $html = uniqid('html');
        $view = $this->createView($html);

        $this->container->method('get')->willReturn($view);

        $actual = $this->fixture->render(uniqid());

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals($html, (string) $actual->getBody());
    }

    /**
     * Creates a URI generator.
     *
     * @param string $uri
     *
     * @return UriGeneratorInterface
     */
    protected function createUriGenerator($uri = '')
    {
        return $this->createConfiguredMock(
            UriGeneratorInterface::class,
            ['generate' => $uri]
        );
    }

    /**
     * Creates a view.
     *
     * @param string $html
     *
     * @return ViewInterface
     */
    protected function createView($html = '')
    {
        return $this->createConfiguredMock(
            ViewInterface::class,
            ['render' => $html]
        );
    }
}
