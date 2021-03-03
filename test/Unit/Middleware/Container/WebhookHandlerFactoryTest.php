<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Primo\Exception\ConfigurationError;
use Primo\Middleware\Container\WebhookHandlerFactory;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class WebhookHandlerFactoryTest extends TestCase
{
    public function testThatAnExceptionIsThrownWhenNoEventDispatcherIsAvailable(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                [EventDispatcherInterface::class, false],
            ]);

        $this->expectException(ConfigurationError::class);
        $this->expectExceptionMessage('I cannot retrieve an event dispatcher from the container');

        $factory = new WebhookHandlerFactory();
        $factory->__invoke($container);
    }

    public function testFactoryWhenDispatcherCanBeFound(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                [EventDispatcherInterface::class, true],
            ]);

        $container->expects(self::once())
            ->method('get')
            ->willReturn($this->createMock(EventDispatcherInterface::class));

        $factory = new WebhookHandlerFactory();
        $factory->__invoke($container);
    }
}
