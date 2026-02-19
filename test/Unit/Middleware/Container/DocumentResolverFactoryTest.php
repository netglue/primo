<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Primo\Middleware\Container\DocumentResolverFactory;
use Primo\Router\RoutingDocumentResolver;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

final class DocumentResolverFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $resolver = $this->createStub(RoutingDocumentResolver::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with(RoutingDocumentResolver::class)
            ->willReturn($resolver);

        $factory = new DocumentResolverFactory();
        $factory->__invoke($container);
    }
}
