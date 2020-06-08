<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Router\Container;

use Primo\Router\Container\RouteParamsFactory;
use PrimoTest\Unit\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class RouteParamsFactoryTest extends TestCase
{
    /** @var RouteParamsFactory */
    private $factory;
    /** @var \PHPUnit\Framework\MockObject\MockObject|ContainerInterface */
    private $container;

    protected function setUp() : void
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new RouteParamsFactory();
    }

    public function testContainerWithoutConfigWillYieldParamsWithDefaultValues() : void
    {
        $this->container->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $this->container->expects($this->never())
            ->method('get')
            ->with('config');

        $params = $this->factory->__invoke($this->container);
        $this->assertSame('document-id', $params->id());
    }

    public function testContainerWithConfiguredParamsWillYieldParamsWithConfiguredValues() : void
    {
        $this->container->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn([
                'primo' => [
                    'router' => [
                        'params' => [
                            'id' => 'a',
                            'uid' => 'b',
                            'type' => 'c',
                            'bookmark' => 'd',
                            'lang' => 'e',
                        ],
                    ],
                ],
            ]);

        $params = $this->factory->__invoke($this->container);
        $this->assertSame('a', $params->id());
        $this->assertSame('b', $params->uid());
        $this->assertSame('c', $params->type());
        $this->assertSame('d', $params->bookmark());
        $this->assertSame('e', $params->lang());
    }
}
