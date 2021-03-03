<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Primo\Middleware\Container\DocumentResolverFactory;
use Primo\Router\DocumentResolver;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class DocumentResolverFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $resolver = $this->createMock(DocumentResolver::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with(DocumentResolver::class)
            ->willReturn($resolver);

        $factory = new DocumentResolverFactory();
        $factory->__invoke($container);
    }
}
