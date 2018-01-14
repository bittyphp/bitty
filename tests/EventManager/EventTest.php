<?php

namespace Bitty\Tests\EventManager;

use Bitty\EventManager\Event;
use Bitty\EventManager\EventInterface;
use Bitty\Tests\TestCase;

class EventTest extends TestCase
{
    /**
     * @var Event
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Event();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(EventInterface::class, $this->fixture);
    }

    public function testGetName()
    {
        $name = uniqid('AB_cd.1234');

        $this->fixture->setName($name);

        $actual = $this->fixture->getName();

        $this->assertEquals($name, $actual);
    }

    public function testSetNameThrowsException()
    {
        $name = uniqid('name').'?';

        $message = 'Event name "'.$name.'" is invalid. Only alpha-numeric '
            .'characters, underscores, and periods allowed';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->setName($name);
    }

    public function testGetTarget()
    {
        $target = uniqid('target');

        $this->fixture->setTarget($target);

        $actual = $this->fixture->getTarget();

        $this->assertEquals($target, $actual);
    }

    public function testGetParams()
    {
        $params = [uniqid(), uniqid()];

        $this->fixture->setParams($params);

        $actual = $this->fixture->getParams();

        $this->assertEquals($params, $actual);
    }

    public function testGetParam()
    {
        $name   = uniqid('name');
        $value  = uniqid('value');
        $params = [uniqid() => uniqid(), $name => $value, uniqid() => uniqid()];

        $this->fixture->setParams($params);

        $actual = $this->fixture->getParam($name);

        $this->assertEquals($value, $actual);
    }

    public function testPropagationStopped()
    {
        $this->fixture->stopPropagation(true);

        $actual = $this->fixture->isPropagationStopped();

        $this->assertTrue($actual);
    }

    public function testPropagationNotStopped()
    {
        $this->fixture->stopPropagation(false);

        $actual = $this->fixture->isPropagationStopped();

        $this->assertFalse($actual);
    }
}
