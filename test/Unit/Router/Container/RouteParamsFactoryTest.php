<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Router\Container;

use Primo\Router\Container\RouteParamsFactory;
use PrimoTest\Unit\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class RouteParamsFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;
    /** @var RouteParamsFactory */
    private $factory;

    protected function setUp() : void
    {
        parent::setUp();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new RouteParamsFactory();
    }

    public function testContainerWithoutConfigWillYieldParamsWithDefaultValues() : void
    {
        $this->container->has('config')->shouldBeCalledOnce()->willReturn(false);
        $this->container->get('config')->shouldNotBeCalled();

        $params = $this->factory->__invoke($this->container->reveal());
        $this->assertSame('document-id', $params->id());
    }

    public function testContainerWithConfiguredParamsWillYieldParamsWithConfiguredValues() : void
    {
        $this->container->has('config')->shouldBeCalledOnce()->willReturn(true);
        $this->container->get('config')->shouldBeCalledOnce()->willReturn([
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

        $params = $this->factory->__invoke($this->container->reveal());
        $this->assertSame('a', $params->id());
        $this->assertSame('b', $params->uid());
        $this->assertSame('c', $params->type());
        $this->assertSame('d', $params->bookmark());
        $this->assertSame('e', $params->lang());
    }
}
