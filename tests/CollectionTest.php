<?php

namespace Bizurkur\Bitty\Tests;

use Bizurkur\Bitty\Collection;
use Bizurkur\Bitty\CollectionInterface;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Collection();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(CollectionInterface::class, $this->fixture);
    }

    public function testAll()
    {
        $count = rand(0, 10);

        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[uniqid('key')] = uniqid('value');
        }

        $this->fixture = new Collection($data);

        $actual = $this->fixture->all();

        $this->assertEquals($data, $actual);
    }

    public function testSet()
    {
        $name  = uniqid();
        $value = uniqid();

        $this->fixture->set($name, $value);
        $actual = $this->fixture->get($name);

        $this->assertEquals($value, $actual);
    }

    /**
     * @dataProvider sampleHasData
     */
    public function testHas($data, $name, $expected)
    {
        $this->fixture = new Collection($data);

        $actual = $this->fixture->has($name);

        $this->assertEquals($expected, $actual);
    }

    public function sampleHasData()
    {
        $name = uniqid('name');

        return [
            'string value' => [
                'data' => [$name => uniqid()],
                'name' => $name,
                'expected' => true,
            ],
            'null value' => [
                'data' => [$name => null],
                'name' => $name,
                'expected' => true,
            ],
            'false value' => [
                'data' => [$name => false],
                'name' => $name,
                'expected' => true,
            ],
            'empty value' => [
                'data' => [$name => ''],
                'name' => $name,
                'expected' => true,
            ],
            'not set' => [
                'data' => [],
                'name' => $name,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider sampleHasData
     */
    public function testRemove($data, $name, $expected)
    {
        $this->fixture = new Collection($data);

        $actualA = $this->fixture->has($name);
        $this->assertEquals($expected, $actualA);

        $this->fixture->remove($name);

        $actualB = $this->fixture->has($name);
        $this->assertFalse($actualB);
    }

    public function testCount()
    {
        $count = rand(0, 10);

        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[uniqid('key')] = uniqid('value');
        }

        $this->fixture = new Collection($data);

        $actual = $this->fixture->count();

        $this->assertEquals($count, $actual);
    }

    /**
     * @dataProvider sampleGetData
     */
    public function testGet($data, $name, $default, $trim, $expected)
    {
        $this->fixture = new Collection($data);

        $actual = $this->fixture->get($name, $default, $trim);

        $this->assertEquals($expected, $actual);
    }

    public function sampleGetData()
    {
        $name    = uniqid('name');
        $value   = uniqid('value');
        $default = uniqid('default');
        $object  = new \stdClass();

        return [
            'string trim' => [
                'data' => [$name => '   '.$value.'   '],
                'name' => $name,
                'default' => $default,
                'trim' => true,
                'expected' => $value,
            ],
            'string no trim' => [
                'data' => [$name => '   '.$value.'   '],
                'name' => $name,
                'default' => $default,
                'trim' => false,
                'expected' => '   '.$value.'   ',
            ],
            'array trim' => [
                'data' => [$name => ['   '.$value.'   ', $object]],
                'name' => $name,
                'default' => $default,
                'trim' => true,
                'expected' => [$value, $object],
            ],
            'array no trim' => [
                'data' => [$name => ['   '.$value.'   ', $object]],
                'name' => $name,
                'default' => $default,
                'trim' => false,
                'expected' => ['   '.$value.'   ', $object],
            ],
            'object trim' => [
                'data' => [$name => $object],
                'name' => $name,
                'default' => $default,
                'trim' => true,
                'expected' => $object,
            ],
            'object no trim' => [
                'data' => [$name => $object],
                'name' => $name,
                'default' => $default,
                'trim' => false,
                'expected' => $object,
            ],
            'null trim' => [
                'data' => [$name => null],
                'name' => $name,
                'default' => $default,
                'trim' => true,
                'expected' => null,
            ],
            'null no trim' => [
                'data' => [$name => null],
                'name' => $name,
                'default' => $default,
                'trim' => false,
                'expected' => null,
            ],
            'default trim' => [
                'data' => [],
                'name' => $name,
                'default' => '   '.$default.'   ',
                'trim' => true,
                'expected' => $default,
            ],
            'default no trim' => [
                'data' => [],
                'name' => $name,
                'default' => '   '.$default.'   ',
                'trim' => false,
                'expected' => '   '.$default.'   ',
            ],
        ];
    }
}
