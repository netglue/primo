<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Container;

use Primo\Cache\PrismicApiCache;
use Primo\Container\ApiFactory;
use Primo\Exception\ConfigurationError;
use Primo\Http\PrismicHttpClient;
use PrimoTest\Unit\TestCase;
use Prismic\ResultSet\ResultSetFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ApiFactoryTest extends TestCase
{
    public function testAnExceptionIsThrownWhenAnApiUrlHasNotBeenConfigured(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $this->expectException(ConfigurationError::class);
        $this->expectExceptionMessage('An api url cannot be determined');

        $factory = new ApiFactory();
        $factory->__invoke($container);
    }

    public function testFactoryWithNoDependenciesSatisfied(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(6))
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [PrismicHttpClient::class, false],
                [RequestFactoryInterface::class, false],
                [UriFactoryInterface::class, false],
                [ResultSetFactory::class, false],
                [PrismicApiCache::class, false],
            ]);

        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['prismic' => ['api' => 'https://example.com']]);

        $factory = new ApiFactory();
        $factory->__invoke($container);
    }
}
