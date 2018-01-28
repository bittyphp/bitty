<?php

namespace Bitty\Tests\View\Twig;

use Bitty\Security\Context\ContextMapInterface;
use Bitty\Security\User\UserInterface;
use Bitty\Tests\TestCase;
use Bitty\View\Twig\SecurityExtension;
use Psr\Http\Message\ServerRequestInterface;
use Twig_ExtensionInterface;
use Twig_SimpleFunction;

class SecurityExtensionTest extends TestCase
{
    /**
     * @var SecurityExtension
     */
    protected $fixture = null;

    /**
     * @var ContextMapInterface
     */
    protected $context = null;

    /**
     * @var ServerRequestInterface
     */
    protected $request = null;

    protected function setUp()
    {
        parent::setUp();

        $this->context = $this->createMock(ContextMapInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);

        $this->fixture = new SecurityExtension($this->context, $this->request);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(Twig_ExtensionInterface::class, $this->fixture);
    }

    public function testGetFunctions()
    {
        $actual = $this->fixture->getFunctions();

        $this->assertContainsOnlyInstancesOf(Twig_SimpleFunction::class, $actual);
        $this->assertCount(1, $actual);
        $this->assertEquals('is_granted', $actual[0]->getName());
        $this->assertEquals([$this->fixture, 'isGranted'], $actual[0]->getCallable());
    }

    public function testIsGrantedGetsUserFromContext()
    {
        $this->context->expects($this->once())
            ->method('getUser')
            ->with($this->request);

        $this->fixture->isGranted(uniqid());
    }

    public function testIsGrantedWithoutUser()
    {
        $actual = $this->fixture->isGranted(uniqid());

        $this->assertFalse($actual);
    }

    /**
     * @dataProvider sampleIsGranted
     */
    public function testIsGranted($roles, $role, $expected)
    {
        $user = $this->createConfiguredMock(UserInterface::class, ['getRoles' => $roles]);
        $this->context->method('getUser')->willReturn($user);

        $actual = $this->fixture->isGranted($role);

        $this->assertEquals($expected, $actual);
    }

    public function sampleIsGranted()
    {
        $role = uniqid('role');

        return [
            'is granted' => [
                'roles' => [uniqid(), $role, uniqid()],
                'role' => $role,
                'expected' => true,
            ],
            'is not granted' => [
                'roles' => [uniqid(), uniqid()],
                'role' => $role,
                'expected' => false,
            ],
        ];
    }
}
