<?php

namespace Bitty\Tests\View;

use Bitty\Tests\TestCase;
use Bitty\View\AbstractView;
use Bitty\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;

class AbstractViewTest extends TestCase
{
    /**
     * @var AbstractView
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = $this->getMockForAbstractClass(AbstractView::class);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ViewInterface::class, $this->fixture);
    }

    public function testRenderResponse()
    {
        $html     = uniqid('html');
        $template = uniqid('template');
        $data     = [uniqid('param')];

        $this->fixture->expects($this->once())
            ->method('render')
            ->with($template, $data)
            ->willReturn($html);

        $actual = $this->fixture->renderResponse($template, $data);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals($html, (string) $actual->getBody());
    }
}
