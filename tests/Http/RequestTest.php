<?php

namespace Bizurkur\Bitty\Tests\Http;

use Bizurkur\Bitty\Http\Request;
use Bizurkur\Bitty\Http\Uri;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Request();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->fixture);
    }

    public function testGetRequestTarget()
    {
        $actual = $this->fixture->getRequestTarget();

        $this->assertEquals('/', $actual);
    }

    public function testWithRequestTarget()
    {
        $uri = uniqid('path').'?'.uniqid('query');

        $clone = $this->fixture->withRequestTarget($uri);
        $old   = $this->fixture->getRequestTarget();
        $new   = $clone->getRequestTarget();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('/', $old);
        $this->assertEquals($uri, $new);
    }

    public function testWithMethod()
    {
        $method = $this->getValidMethod();

        $clone = $this->fixture->withMethod($method);
        $old   = $this->fixture->getMethod();
        $new   = $clone->getMethod();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('GET', $old);
        $this->assertEquals($method, $new);
    }

    public function testWithMethodThrowsException()
    {
        $method = uniqid();

        $message = 'HTTP method "'.$method.'" is invalid. Valid methods are: '
            .'["OPTIONS", "HEAD", "GET", "POST", "PUT", "PATCH", "DELETE", "TRACE", "CONNECT"]';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->withMethod($method);
    }

    /**
     * @dataProvider sampleUris
     */
    public function testWithUri($headers, $uri, $preserveHost, $expected)
    {
        $this->fixture = new Request('GET', '', $headers);

        $clone = $this->fixture->withUri(new Uri($uri), $preserveHost);
        $old   = $this->fixture->getUri();
        $new   = $clone->getUri();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('/', (string) $old);
        $this->assertEquals($uri, (string) $new);
        $this->assertEquals($expected, $clone->getHeaderLine('Host'));
    }

    public function sampleUris()
    {
        $host = uniqid('host');

        return [
            'nothing set' => [
                'headers' => [],
                'uri' => '/',
                'preserveHost' => false,
                'expected' => '',
            ],
            'preserved, host and header set' => [
                'headers' => ['Host' => $host],
                'uri' => 'http://example.com/',
                'preserveHost' => true,
                'expected' => $host,
            ],
            'preserved, host set' => [
                'headers' => [],
                'uri' => 'http://example.com/',
                'preserveHost' => true,
                'expected' => 'example.com',
            ],
            'preserved, header set' => [
                'headers' => ['Host' => $host],
                'uri' => '/',
                'preserveHost' => true,
                'expected' => $host,
            ],
            'not preserved, host set' => [
                'headers' => ['Host' => $host],
                'uri' => 'http://example.com/',
                'preserveHost' => false,
                'expected' => 'example.com',
            ],
            'not preserved, host not set' => [
                'headers' => ['Host' => $host],
                'uri' => '/',
                'preserveHost' => false,
                'expected' => $host,
            ],
        ];
    }

    public function testWithQueryParams()
    {
        $params = [uniqid() => uniqid()];

        $clone = $this->fixture->withQueryParams($params);
        $old   = $this->fixture->getQueryParams();
        $new   = $clone->getQueryParams();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([], $old);
        $this->assertEquals($params, $new);
    }

    public function testWithCookieParams()
    {
        $params = [uniqid() => uniqid()];

        $clone = $this->fixture->withCookieParams($params);
        $old   = $this->fixture->getCookieParams();
        $new   = $clone->getCookieParams();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([], $old);
        $this->assertEquals($params, $new);
    }

    public function testWithUploadedFiles()
    {
        $files = [uniqid() => $this->createMock(UploadedFileInterface::class)];

        $clone = $this->fixture->withUploadedFiles($files);
        $old   = $this->fixture->getUploadedFiles();
        $new   = $clone->getUploadedFiles();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals([], $old);
        $this->assertEquals($files, $new);
    }

    public function testGetServerParams()
    {
        $params  = [uniqid() => uniqid()];
        $fixture = new Request('GET', '', [], [], [], [], [], $params, []);

        $actual = $fixture->getServerParams();

        $this->assertEquals($params, $actual);
    }

    /**
     * @dataProvider samplePostContentTypes
     */
    public function testGetParsedBody($method, $contentType, $request, $body, $expected)
    {
        $headers = ['Content-Type' => $contentType];
        $fixture = new Request($method, '', $headers, [], $request, [], [], [], [], $body);

        $actual = $fixture->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function samplePostContentTypes()
    {
        $params = [uniqid('key') => uniqid('value')];
        $json   = [uniqid('json') => uniqid('value')];

        return [
            'POST application/x-www-form-urlencoded' => [
                'method' => 'POST',
                'contentType' => 'application/x-www-form-urlencoded',
                'request' => $params,
                'body' => '',
                'expected' => $params
            ],
            'multipart/form-data' => [
                'method' => 'POST',
                'contentType' => 'multipart/form-data',
                'request' => $params,
                'body' => '',
                'expected' => $params
            ],
            'non-POST application/x-www-form-urlencoded' => [
                'method' => 'PATCH',
                'contentType' => 'application/x-www-form-urlencoded',
                'request' => $params,
                'body' => 'foo=bar&baz=bur',
                'expected' => ['foo' => 'bar', 'baz' => 'bur']
            ],
            'valid application/json' => [
                'method' => 'POST',
                'contentType' => 'application/json',
                'request' => $params,
                'body' => json_encode($json),
                'expected' => $json
            ],
            'invalid application/json' => [
                'method' => 'POST',
                'contentType' => 'application/json',
                'request' => $params,
                'body' => '[}',
                'expected' => null
            ],
            'unknown' => [
                'method' => 'POST',
                'contentType' => uniqid(),
                'request' => $params,
                'body' => json_encode($json),
                'expected' => null
            ],
        ];
    }

    /**
     * @dataProvider sampleValidParsedBody
     */
    public function testWithParsedBody($parsedBody)
    {
        $clone = $this->fixture->withParsedBody($parsedBody);
        $old   = $this->fixture->getParsedBody();
        $new   = $clone->getParsedBody();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals(null, $old);
        $this->assertEquals($parsedBody, $new);
    }

    public function sampleValidParsedBody()
    {
        return [
            'object' => [new \stdClass()],
            'array' => [[uniqid()]],
            'null' => [null],
        ];
    }

    public function testWithParsedBodyInvalid()
    {
        $message = 'Parsed body must be an array, object, or null; integer given.';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->withParsedBody(rand());
    }

    public function testGetAttributes()
    {
        $attributes = [uniqid() => uniqid()];
        $fixture    = new Request('GET', '', [], [], [], [], [], [], $attributes);

        $actual = $fixture->getAttributes();

        $this->assertEquals($attributes, $actual);
    }

    /**
     * @dataProvider sampleAttributes
     */
    public function testGetAttribute($attributes, $name, $default, $expected)
    {
        $fixture = new Request('GET', '', [], [], [], [], [], [], $attributes);

        $actual = $fixture->getAttribute($name, $default);

        $this->assertEquals($expected, $actual);
    }

    public function sampleAttributes()
    {
        $nameA  = uniqid('name');
        $nameB  = uniqid('name');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        return [
            'existing attribute' => [
                'attributes' => [$nameA => $valueA, $nameB => $valueB],
                'name' => $nameB,
                'default' => uniqid(),
                'expected' => $valueB,
            ],
            'non-existing attribute' => [
                'attributes' => [$nameA => $valueA],
                'name' => uniqid(),
                'default' => $valueB,
                'expected' => $valueB,
            ],
        ];
    }

    public function testWithAttribute()
    {
        $name  = uniqid('name');
        $value = uniqid('value');

        $clone = $this->fixture->withAttribute($name, $value);
        $old   = $this->fixture->getAttribute($name);
        $new   = $clone->getAttribute($name);

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals(null, $old);
        $this->assertEquals($value, $new);
    }

    public function testWithoutAttribute()
    {
        $name       = uniqid('name');
        $attributes = [$name => uniqid()];
        $fixture    = new Request('GET', '', [], [], [], [], [], [], $attributes);

        $clone = $this->fixture->withoutAttribute($name);
        $old   = $this->fixture->getAttribute($name);
        $new   = $clone->getAttribute($name);

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals(null, $old);
        $this->assertEquals(null, $new);
    }

    public function testWithoutNonExistentAttribute()
    {
        $name = uniqid('name');

        $clone  = $this->fixture->withoutAttribute($name);
        $actual = $clone->getAttribute($name);

        $this->assertEquals(null, $actual);
    }

    public function testRegisterContentTypeParser()
    {
        $contentType = uniqid('type');
        $callback    = function () {
            return ['decoded'];
        };

        $headers       = ['Content-Type' => $contentType];
        $this->fixture = new Request('GET', '', $headers);
        $this->fixture->registerContentTypeParser($contentType, $callback);

        $actual = $this->fixture->getParsedBody();

        $this->assertEquals(['decoded'], $actual);
    }

    public function testRegisterContentTypeParserThrowsException()
    {
        $contentType = uniqid('type');

        $message = 'Callback for "'.$contentType.'" must be a callable; string given.';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->registerContentTypeParser($contentType, uniqid());
    }

    protected function getValidMethod()
    {
        $validMethods = [
            'OPTIONS',
            'HEAD',
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'TRACE',
            'CONNECT',
        ];

        $key = array_rand($validMethods);

        return $validMethods[$key];
    }
}
