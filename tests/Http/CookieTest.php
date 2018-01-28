<?php

namespace Bitty\Tests\Http;

use Bitty\Http\Cookie;
use Bitty\Tests\TestCase;

class CookieTest extends TestCase
{
    /**
     * @var Cookie
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Cookie('');
    }

    public function testSetName()
    {
        $name = uniqid('name');

        $this->fixture->setName($name);

        $actual = $this->fixture->getName();

        $this->assertEquals($name, $actual);
    }

    public function testSetValue()
    {
        $value = uniqid('value');

        $this->fixture->setValue($value);

        $actual = $this->fixture->getValue();

        $this->assertEquals($value, $actual);
    }

    public function testSetExpires()
    {
        $expires = rand();

        $this->fixture->setExpires($expires);

        $actual = $this->fixture->getExpires();

        $this->assertEquals($expires, $actual);
    }

    public function testSetPath()
    {
        $path = uniqid('path');

        $this->fixture->setPath($path);

        $actual = $this->fixture->getPath();

        $this->assertEquals($path, $actual);
    }

    public function testSetDomain()
    {
        $domain = uniqid('domain');

        $this->fixture->setDomain($domain);

        $actual = $this->fixture->getDomain();

        $this->assertEquals($domain, $actual);
    }

    public function testSetSecure()
    {
        $secure = (bool) rand(0, 1);

        $this->fixture->setSecure($secure);

        $actual = $this->fixture->isSecure();

        $this->assertEquals($secure, $actual);
    }

    public function testSetHttpOnly()
    {
        $httpOnly = (bool) rand(0, 1);

        $this->fixture->setHttpOnly($httpOnly);

        $actual = $this->fixture->isHttpOnly();

        $this->assertEquals($httpOnly, $actual);
    }

    public function testRaw()
    {
        $raw = (bool) rand(0, 1);

        $this->fixture->setRaw($raw);

        $actual = $this->fixture->isRaw();

        $this->assertEquals($raw, $actual);
    }

    /**
     * @dataProvider sampleStrings
     */
    public function testToString($methods, $expected)
    {
        foreach ($methods as $method => $value) {
            call_user_func([$this->fixture, $method], $value);
        }

        $this->assertEquals($expected, (string) $this->fixture);
    }

    public function sampleStrings()
    {
        $name   = uniqid('name');
        $value  = uniqid('value');
        $path   = uniqid('path');
        $domain = uniqid('domain');

        return [
            'deleted' => [
                'methods' => [
                    'setName' => $name,
                    'setValue' => 'deleted',
                    'setExpires' => 1483288303,
                ],
                'expected' => $name.'=deleted; expires=Sun, 01-Jan-2017 16:31:43 GMT; httponly',
            ],
            'session cookie' => [
                'methods' => [
                    'setName' => $name,
                    'setValue' => $value,
                ],
                'expected' => $name.'='.$value.'; httponly',
            ],
            'path included' => [
                'methods' => [
                    'setName' => $name,
                    'setValue' => $value,
                    'setPath' => $path,
                ],
                'expected' => $name.'='.$value.'; path='.$path.'; httponly',
            ],
            'domain included' => [
                'methods' => [
                    'setName' => $name,
                    'setValue' => $value,
                    'setDomain' => $domain,
                ],
                'expected' => $name.'='.$value.'; domain='.$domain.'; httponly',
            ],
            'secure only' => [
                'methods' => [
                    'setName' => $name,
                    'setValue' => $value,
                    'setSecure' => true,
                ],
                'expected' => $name.'='.$value.'; secure; httponly',
            ],
            'allow script access' => [
                'methods' => [
                    'setName' => $name,
                    'setValue' => $value,
                    'setHttpOnly' => false,
                ],
                'expected' => $name.'='.$value.'; ',
            ],
            'name and value encoded' => [
                'methods' => [
                    'setName' => $name.'^',
                    'setValue' => $value.'^',
                ],
                'expected' => $name.'%5E='.$value.'%5E; httponly',
            ],
            'raw is not encoded' => [
                'methods' => [
                    'setName' => $name.'^',
                    'setValue' => $value.'^',
                    'setRaw' => true,
                ],
                'expected' => $name.'^='.$value.'^; httponly',
            ],
            'mixed' => [
                'methods' => [
                    'setName' => $name,
                    'setValue' => $value,
                    'setExpires' => 1514851200,
                    'setPath' => $path,
                    'setDomain' => $domain,
                    'setSecure' => true,
                ],
                'expected' => $name.'='.$value.'; expires=Tue, 02-Jan-2018 00:00:00 GMT; '
                    .'path='.$path.'; domain='.$domain.'; secure; httponly',
            ],
        ];
    }
}
