<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Router\Container;

use Primo\Router\Container\DocumentResolverFactory;
use Primo\Router\RouteParams;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Psr\Container\ContainerInterface;

final class DocumentResolverFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $api = $this->createStub(ApiClient::class);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                [ApiClient::class, $api],
                [RouteParams::class, RouteParams::fromArray([])],
            ]);

        $factory = new DocumentResolverFactory();
        $factory->__invoke($container);
    }
}
