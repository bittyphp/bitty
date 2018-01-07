<?php

namespace Bizurkur\Bitty\Tests;

use Bizurkur\Bitty\Application;
use Bizurkur\Bitty\Container\Container;
use Bizurkur\Bitty\Container\ContainerInterface;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandler;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Http\Stream;
use Bizurkur\Bitty\Router\RouterInterface;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    protected $fixture = null;

    /**
     * @var Container
     */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->createContainer();

        $this->fixture = new Application($this->container);
    }

    /**
     * @dataProvider sampleDefaultServices
     */
    public function testDefaultServicesSet($id, $className)
    {
        $fixture   = new Application();
        $container = $fixture->getContainer();

        $this->assertInstanceOf($className, $container->get($id));
    }

    /**
     * @dataProvider sampleDefaultServices
     */
    public function testDefaultServicesSetOnCustomContainer($id, $className)
    {
        $container = new Container();
        $fixture   = new Application($container);

        $this->assertInstanceOf($className, $container->get($id));
    }

    /**
     * @dataProvider sampleDefaultServices
     */
    public function testDefaultServicesNotReset($id, $className)
    {
        $service = $this->createMock($className);

        $container = new Container();
        $container->set($id, $service);

        new Application($container);

        $this->assertSame($service, $container->get($id));
    }

    public function sampleDefaultServices()
    {
        return [
            'router' => ['router', RouterInterface::class],
            'request_handler' => ['request_handler', RequestHandlerInterface::class],
            'request' => ['request', ServerRequestInterface::class],
            'response' => ['response', ResponseInterface::class],
        ];
    }

    public function testGetContainer()
    {
        $actual = $this->fixture->getContainer();

        $this->assertSame($this->container, $actual);
    }

    public function testRunSetsRequestHandlerContainer()
    {
        $requestHandler = $this->createMock(RequestHandler::class);
        $this->setUpDependencies(null, null, $requestHandler);

        $requestHandler->expects($this->once())
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->run();
    }

    public function testRunDoesNotSetsRequestHandlerContainer()
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->setUpDependencies(null, null, $requestHandler);

        $requestHandler->expects($this->never())->method('setContainer');

        $this->fixture->run();
    }

    public function testRunCallsRequestHandlerHandle()
    {
        $request        = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->setUpDependencies($request, null, $requestHandler);

        $requestHandler->expects($this->once())
            ->method('handle')
            ->with($request);

        $this->fixture->run();
    }

    public function testRunCallsMiddleware()
    {
        $request        = $this->createMock(ServerRequestInterface::class);
        $response       = $this->createMock(ResponseInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->setUpDependencies($request, null, $requestHandler);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middleware);

        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturn($response);

        $this->fixture->run();
    }

    /**
     * @runInSeparateProcess
     * @dataProvider sampleHeaders
     */
    public function testRunSetsResponseHeaders($headers, $expected)
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->markTestSkipped('xdebug_get_headers() is not available.');

            return;
        }

        $response = $this->createResponse($headers);
        $this->setUpDependencies(null, $response, null);

        $this->fixture->run();
        $actual = xdebug_get_headers();

        $this->assertEquals($expected, $actual);
    }

    public function sampleHeaders()
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $valueC  = uniqid('value');
        $valueD  = uniqid('value');

        return [
            'no headers' => [
                'headers' => [],
                'expected' => [],
            ],
            'single header, single value' => [
                'headers' => [$headerA => [$valueA]],
                'expected' => [$headerA.': '.$valueA],
            ],
            'single header, multiple values' => [
                'headers' => [$headerA => [$valueA, $valueB]],
                'expected' => [$headerA.': '.$valueA, $headerA.': '.$valueB],
            ],
            'multiple headers, single values' => [
                'headers' => [$headerA => [$valueA], $headerB => [$valueB]],
                'expected' => [$headerA.': '.$valueA, $headerB.': '.$valueB],
            ],
            'multiple headers, multiple values' => [
                'headers' => [
                    $headerA => [$valueA, $valueB],
                    $headerB => [$valueC, $valueD],
                ],
                'expected' => [
                    $headerA.': '.$valueA,
                    $headerA.': '.$valueB,
                    $headerB.': '.$valueC,
                    $headerB.': '.$valueD,
                ],
            ],
        ];
    }

    public function testRunOutputsResponseBody()
    {
        $body     = uniqid('body');
        $response = $this->createResponse([], $body);
        $this->setUpDependencies(null, $response);

        ob_start();
        $this->fixture->run();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($body, $actual);
    }

    /**
     * Creates a container.
     *
     * @return ContainerInterface
     */
    protected function createContainer()
    {
        return $this->createConfiguredMock(
            ContainerInterface::class,
            ['has' => true]
        );
    }

    /**
     * Creates a response.
     *
     * @param array $headers
     * @param string $body
     *
     * @return ResponseInterface
     */
    protected function createResponse(array $headers = [], string $body = '')
    {
        return $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getHeaders' => $headers,
                'getBody' => new Stream($body),
            ]
        );
    }

    /**
     * Sets up dependencies.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param RequestHandlerInterface $requestHandler
     */
    protected function setUpDependencies(
        ServerRequestInterface $request = null,
        ResponseInterface $response = null,
        RequestHandlerInterface $requestHandler = null
    ) {
        if (null === $request) {
            $request = $this->createMock(ServerRequestInterface::class);
        }
        if (null === $response) {
            $response = $this->createMock(ResponseInterface::class);
        }
        if (null === $requestHandler) {
            $requestHandler = $this->createMock(RequestHandlerInterface::class);
        }

        $requestHandler->method('handle')->willReturn($response);

        $this->container->method('get')
            ->willReturnMap(
                [
                    ['request', $request],
                    ['request_handler', $requestHandler],
                ]
            );
    }
}
