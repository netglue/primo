<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Primo\Middleware\Container\PreviewHandlerFactory;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Prismic\LinkResolver;
use Psr\Container\ContainerInterface;

class PreviewHandlerFactoryTest extends TestCase
{
    public function testFactoryExecutesWhenConfigIsNotAvailable() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $map = [
            [
                ApiClient::class,
                $this->createMock(ApiClient::class),
            ],
            [
                LinkResolver::class,
                $this->createMock(LinkResolver::class),
            ],
        ];

        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap($map);

        $factory = new PreviewHandlerFactory();
        $factory->__invoke($container);
    }
}
