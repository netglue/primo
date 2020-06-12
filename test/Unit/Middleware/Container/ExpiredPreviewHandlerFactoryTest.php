<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Primo\Middleware\Container\ExpiredPreviewHandlerFactory;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class ExpiredPreviewHandlerFactoryTest extends TestCase
{
    public function testFactoryWhenNoConfigIsAvailable() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $factory = new ExpiredPreviewHandlerFactory();
        $factory($container);
    }
}
