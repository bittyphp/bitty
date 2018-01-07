<?php

namespace Bizurkur\Bitty\Tests\Http;

use Bizurkur\Bitty\Collection;
use Bizurkur\Bitty\Http\Uri;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\UriInterface;

class UriTest extends TestCase
{
    /**
     * @var Uri
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Uri();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(UriInterface::class, $this->fixture);
    }

    /**
     * @dataProvider sampleUriData
     */
    public function testUriData($uri, $expected)
    {
        $fixture = new Uri($uri);

        $actual = [
            'scheme' => $fixture->getScheme(),
            'userInfo' => $fixture->getUserInfo(),
            'host' => $fixture->getHost(),
            'port' => $fixture->getPort(),
            'path' => $fixture->getPath(),
            'query' => $fixture->getQuery(),
            'fragment' => $fixture->getFragment(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function sampleUriData()
    {
        return [
            'full uri' => [
                'uri' => 'http://user:pass@eXaMpLe.com:1234/path/to/file?foo=bar#baz',
                'expected' => [
                    'scheme' => 'http',
                    'userInfo' => 'user:pass',
                    'host' => 'example.com',
                    'port' => 1234,
                    'path' => '/path/to/file',
                    'query' => 'foo=bar',
                    'fragment' => 'baz',
                ],
            ],
            'no scheme' => [
                'uri' => '//eXaMpLe.com/path/to/file?foo=bar#baz',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/path/to/file',
                    'query' => 'foo=bar',
                    'fragment' => 'baz',
                ],
            ],
            'no host' => [
                'uri' => '/path/to/file?foo=bar#baz',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => '',
                    'port' => null,
                    'path' => '/path/to/file',
                    'query' => 'foo=bar',
                    'fragment' => 'baz',
                ],
            ],
            'query string only' => [
                'uri' => '?foo=bar',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => '',
                    'port' => null,
                    'path' => '',
                    'query' => 'foo=bar',
                    'fragment' => '',
                ],
            ],
            'host only' => [
                'uri' => '//example.com',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                ],
            ],
            'failure' => [
                'uri' => 'http:///example.com',
                'expected' => [
                    'scheme' => '',
                    'userInfo' => '',
                    'host' => '',
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider sampleUriStringData
     */
    public function testToString($uri, $expected)
    {
        $fixture = new Uri($uri);

        $this->assertEquals($expected, (string) $fixture);
    }

    public function sampleUriStringData()
    {
        return [
            'blank' => [
                'uri' => '',
                'expected' => '/',
            ],
            'file, no host' => [
                'uri' => 'file:///path/to/file',
                'expected' => 'file:///path/to/file',
            ],
            'http, no host' => [
                'uri' => 'http:/path/to/file',
                'expected' => 'http://localhost/path/to/file',
            ],
            'https, no host' => [
                'uri' => 'https:/path/to/file',
                'expected' => 'https://localhost/path/to/file',
            ],
            'http, default port' => [
                'uri' => 'http://example.com:80/',
                'expected' => 'http://example.com/',
            ],
            'http, non-default port' => [
                'uri' => 'http://example.com:443/',
                'expected' => 'http://example.com:443/',
            ],
            'https, default port' => [
                'uri' => 'https://example.com:443/',
                'expected' => 'https://example.com/',
            ],
            'https, non-default port' => [
                'uri' => 'https://example.com:80/',
                'expected' => 'https://example.com:80/',
            ],
            'user only' => [
                'uri' => 'ftp://user@example.com/',
                'expected' => 'ftp://user@example.com/',
            ],
            'user and pass' => [
                'uri' => 'ftp://user:pass@example.com:21/',
                'expected' => 'ftp://user:pass@example.com/',
            ],
            'query and fragment' => [
                'uri' => '/path/to/file?foo=bar#baz',
                'expected' => '/path/to/file?foo=bar#baz',
            ],
        ];
    }

    public function testWithScheme()
    {
        $scheme = uniqid('a+.-');

        $clone = $this->fixture->withScheme(strtoupper($scheme).':');

        $old = $this->fixture->getScheme();
        $new = $clone->getScheme();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('', $old);
        $this->assertEquals($scheme, $new);
    }

    public function testWithSchemeThrowsException()
    {
        $scheme = rand();

        $message = 'Invalid scheme "'.$scheme.'".';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->withScheme($scheme);
    }

    public function testWithUserInfoNoPass()
    {
        $user = uniqid('user');

        $clone = $this->fixture->withUserInfo($user);

        $old = $this->fixture->getUserInfo();
        $new = $clone->getUserInfo();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('', $old);
        $this->assertEquals($user, $new);
    }

    public function testWithUserInfo()
    {
        $user = uniqid('user');
        $pass = uniqid('pass');

        $clone = $this->fixture->withUserInfo($user, $pass);

        $old = $this->fixture->getUserInfo();
        $new = $clone->getUserInfo();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('', $old);
        $this->assertEquals($user.':'.$pass, $new);
    }

    public function testWithHost()
    {
        $host = uniqid('host');

        $clone = $this->fixture->withHost(strtoupper($host));

        $old = $this->fixture->getHost();
        $new = $clone->getHost();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('', $old);
        $this->assertEquals($host, $new);
    }

    public function testWithPort()
    {
        $port = rand(0, 65535);

        $clone = $this->fixture->withPort($port);

        $old = $this->fixture->getPort();
        $new = $clone->getPort();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertNull($old);
        $this->assertEquals($port, $new);
    }

    /**
     * @dataProvider sampleInvalidPorts
     */
    public function testWithPortThrowsException($port)
    {
        $message = 'Invalid port '.$port.'. Must be between 1 and 65,535.';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->withPort($port);
    }

    public function sampleInvalidPorts()
    {
        return [
            'too high' => [rand(65536, 99999)],
            'too low' => [-rand(1, 99999)],
        ];
    }

    /**
     * @dataProvider samplePaths
     */
    public function testWithPath($path, $expected)
    {
        $clone = $this->fixture->withPath($path);

        $old = $this->fixture->getPath();
        $new = $clone->getPath();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('', $old);
        $this->assertEquals($expected, $new);
    }

    public function samplePaths()
    {
        $path = uniqid('path');

        return [
            'empty path' => [
                'path' => '',
                'expected' => '',
            ],
            'normal path' => [
                'path' => $path,
                'expected' => $path,
            ],
            'invalid characters encoded' => [
                'path' => $path.'/^'.$path,
                'expected' => $path.'/%5E'.$path,
            ],
            'encoded characters not double encoded' => [
                'path' => $path.'/%5E'.$path,
                'expected' => $path.'/%5E'.$path,
            ],
        ];
    }

    /**
     * @dataProvider sampleQueries
     */
    public function testWithQuery($query, $expected)
    {
        $clone = $this->fixture->withQuery($query);

        $old = $this->fixture->getQuery();
        $new = $clone->getQuery();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('', $old);
        $this->assertEquals($expected, $new);
    }

    public function sampleQueries()
    {
        $keyA   = uniqid('key');
        $keyB   = uniqid('key');
        $valueA = uniqid('value');
        $valueB = uniqid('value');

        return [
            'empty query' => [
                'query' => '',
                'expected' => '',
            ],
            'leading ? removed' => [
                'query' => '?'.$keyA.'='.$valueA,
                'expected' => $keyA.'='.$valueA,
            ],
            'normal query' => [
                'query' => $keyA.'='.$valueA.'&'.$keyB.'='.$valueB,
                'expected' => $keyA.'='.$valueA.'&'.$keyB.'='.$valueB,
            ],
            'invalid characters encoded' => [
                'query' => $keyA.'^'.'='.$valueA.'&'.$keyB.'=^'.$valueB,
                'expected' => $keyA.'%5E'.'='.$valueA.'&'.$keyB.'=%5E'.$valueB,
            ],
            'encoded characters not double encoded' => [
                'query' => $keyA.'%5E'.'='.$valueA.'&'.$keyB.'=%5E'.$valueB,
                'expected' => $keyA.'%5E'.'='.$valueA.'&'.$keyB.'=%5E'.$valueB,
            ],
        ];
    }

    /**
     * @dataProvider sampleFragments
     */
    public function testWithFragment($fragment, $expected)
    {
        $clone = $this->fixture->withFragment($fragment);

        $old = $this->fixture->getFragment();
        $new = $clone->getFragment();

        $this->assertNotSame($this->fixture, $clone);
        $this->assertEquals('', $old);
        $this->assertEquals($expected, $new);
    }

    public function sampleFragments()
    {
        $value = uniqid('value');

        return [
            'empty fragment' => [
                'fragment' => '',
                'expected' => '',
            ],
            'leading # removed' => [
                'fragment' => '#'.$value,
                'expected' => $value,
            ],
            'normal fragment' => [
                'fragment' => $value,
                'expected' => $value,
            ],
            'invalid characters encoded' => [
                'fragment' => $value.'^'.$value,
                'expected' => $value.'%5E'.$value,
            ],
            'encoded characters not double encoded' => [
                'fragment' => $value.'%5E'.$value,
                'expected' => $value.'%5E'.$value,
            ],
        ];
    }

    /**
     * @dataProvider sampleEnvironmentData
     */
    public function testCreateFromEnvironment($data, $expected)
    {
        $uri = Uri::createFromEnvironment(new Collection($data));

        $this->assertEquals($expected, (string) $uri);
    }

    public function sampleEnvironmentData()
    {
        $user  = uniqid('user');
        $pass  = uniqid('pass');
        $host  = uniqid('host');
        $port  = rand(444, 65535);
        $path  = uniqid('path');
        $query = uniqid('query');

        return [
            'standard data' => [
                'data' => [
                    'PHP_AUTH_USER' => $user,
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'https on' => [
                'data' => [
                    'HTTPS' => uniqid(),
                    'PHP_AUTH_USER' => $user,
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'https://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'https off' => [
                'data' => [
                    'HTTPS' => 'off',
                    'PHP_AUTH_USER' => $user,
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'no password' => [
                'data' => [
                    'PHP_AUTH_USER' => $user,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$user.'@'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'no user, with pass' => [
                'data' => [
                    'PHP_AUTH_PW' => $pass,
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'port set from host header' => [
                'data' => [
                    'HTTP_HOST' => $host.':'.$port,
                    'SERVER_PORT' => rand(),
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'host set from server name' => [
                'data' => [
                    'SERVER_NAME' => $host,
                    'SERVER_PORT' => $port,
                    'REQUEST_URI' => '/'.$path.'?'.$query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path.'?'.$query,
            ],
            'path set from path info' => [
                'data' => [
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'PATH_INFO' => $path,
                ],
                'expected' => 'http://'.$host.':'.$port.'/'.$path,
            ],
            'query set from query string' => [
                'data' => [
                    'HTTP_HOST' => $host,
                    'SERVER_PORT' => $port,
                    'QUERY_STRING' => $query,
                ],
                'expected' => 'http://'.$host.':'.$port.'/?'.$query,
            ],
        ];
    }
}
