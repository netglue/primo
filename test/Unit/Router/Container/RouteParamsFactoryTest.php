<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Router\Container;

use PHPUnit\Framework\MockObject\MockObject;
use Primo\Router\Container\RouteParamsFactory;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

/** @psalm-suppress DeprecatedMethod */
class RouteParamsFactoryTest extends TestCase
{
    private RouteParamsFactory $factory;
    /** @var MockObject&ContainerInterface */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new RouteParamsFactory();
    }

    public function testContainerWithoutConfigWillYieldParamsWithDefaultValues(): void
    {
        $this->container->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $this->container->expects(self::never())
            ->method('get')
            ->with('config');

        $params = $this->factory->__invoke($this->container);
        self::assertSame('document-id', $params->id());
    }

    public function testContainerWithConfiguredParamsWillYieldParamsWithConfiguredValues(): void
    {
        $this->container->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $this->container->expects(self::once())
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
        self::assertSame('a', $params->id());
        self::assertSame('b', $params->uid());
        self::assertSame('c', $params->type());
        self::assertSame('d', $params->bookmark());
        self::assertSame('e', $params->lang());
    }
}
