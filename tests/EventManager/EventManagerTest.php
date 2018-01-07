<?php

namespace Bizurkur\Bitty\Tests\EventManager;

use Bizurkur\Bitty\EventManager\Event;
use Bizurkur\Bitty\EventManager\EventInterface;
use Bizurkur\Bitty\EventManager\EventManager;
use Bizurkur\Bitty\EventManager\EventManagerInterface;
use Bizurkur\Bitty\Tests\Stubs\InvokableStubInterface;
use Bizurkur\Bitty\Tests\TestCase;

class EventManagerTest extends TestCase
{
    /**
     * @var EventManager
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new EventManager();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(EventManagerInterface::class, $this->fixture);
    }

    public function testAttachReturnsTrue()
    {
        $actual = $this->fixture->attach(uniqid(), $this->createCallback());

        $this->assertTrue($actual);
    }

    public function testCorrectCallbacksTriggered()
    {
        $nameA     = uniqid('name');
        $nameB     = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();
        $callbackC = $this->createCallback();
        $callbackD = $this->createCallback();

        $this->fixture->attach($nameA, $callbackA);
        $this->fixture->attach($nameB, $callbackB);
        $this->fixture->attach($nameB, $callbackC);
        $this->fixture->attach($nameA, $callbackD);

        $callbackB->expects($this->never())->method('__invoke');
        $callbackC->expects($this->never())->method('__invoke');

        $callbackA->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(EventInterface::class));

        $callbackD->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(EventInterface::class));

        $this->fixture->trigger($nameA);
    }

    public function testCallbacksTriggeredInCorrectOrder()
    {
        $name      = uniqid('name');
        $callbackA = $this->createCallback('A');
        $callbackB = $this->createCallback('B');
        $callbackC = $this->createCallback('C');
        $callbackD = $this->createCallback('D');

        $this->fixture->attach($name, $callbackA);
        $this->fixture->attach($name, $callbackB, 10);
        $this->fixture->attach($name, $callbackC, -10);
        $this->fixture->attach($name, $callbackD);

        $event = new Event($name);
        $this->fixture->trigger($event);

        $this->assertEquals(['B', 'A', 'D', 'C'], $event->getParams());
    }

    public function testDetachCallback()
    {
        $name      = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();
        $callbackC = $this->createCallback();

        $this->fixture->attach($name, $callbackA);
        $this->fixture->attach($name, $callbackB);
        $this->fixture->attach($name, $callbackC);
        $this->fixture->detach($name, $callbackB);

        $callbackB->expects($this->never())->method('__invoke');

        $callbackA->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(EventInterface::class));

        $callbackC->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(EventInterface::class));

        $this->fixture->trigger($name);
    }

    public function testDetachSuccess()
    {
        $name     = uniqid('name');
        $callback = $this->createCallback();

        $this->fixture->attach($name, $callback);

        $actual = $this->fixture->detach($name, $callback);

        $this->assertTrue($actual);
    }

    public function testDetachNonExistentEvent()
    {
        $actual = $this->fixture->detach(uniqid(), $this->createCallback());

        $this->assertFalse($actual);
    }

    public function testDetachNonExistentCallback()
    {
        $name      = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();

        $this->fixture->attach($name, $callbackA);

        $actual = $this->fixture->detach($name, $callbackB);

        $this->assertFalse($actual);
    }

    public function testClearListeners()
    {
        $nameA     = uniqid('name');
        $nameB     = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();
        $callbackC = $this->createCallback();

        $this->fixture->attach($nameA, $callbackA);
        $this->fixture->attach($nameB, $callbackB);
        $this->fixture->attach($nameA, $callbackC);

        $this->fixture->clearListeners($nameA);

        $callbackA->expects($this->never())->method('__invoke');
        $callbackC->expects($this->never())->method('__invoke');

        $this->fixture->trigger($nameA);

        $callbackB->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(EventInterface::class));

        $this->fixture->trigger($nameB);
    }

    public function testClearNonExistentListeners()
    {
        $actual = $this->fixture->clearListeners(uniqid());

        $this->assertNull($actual);
    }

    public function testTriggerNonExistentEvent()
    {
        $actual = $this->fixture->trigger(uniqid());

        $this->assertFalse($actual);
    }

    public function testEventPropagationStopped()
    {
        $name      = uniqid('name');
        $callbackA = $this->createMock(InvokableStubInterface::class);
        $callbackA->method('__invoke')->willReturnCallback(
            function (EventInterface $event) {
                $event->stopPropagation(true);
            }
        );
        $callbackB = $this->createCallback();

        $this->fixture->attach($name, $callbackA);
        $this->fixture->attach($name, $callbackB);

        $callbackA->expects($this->once())->method('__invoke');
        $callbackB->expects($this->never())->method('__invoke');

        $this->fixture->trigger($name);
    }

    /**
     * Creates a callback.
     *
     * @param string|null $name
     *
     * @return InvokableStubInterface
     */
    protected function createCallback($name = null)
    {
        $callback = $this->createMock(InvokableStubInterface::class);
        $callback->method('__invoke')->willReturnCallback(
            function (EventInterface $event) use ($name) {
                $params   = $event->getParams();
                $params[] = $name;
                $event->setParams($params);
            }
        );

        return $callback;
    }
}
