<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Primo\Middleware\Container\PreviewCacheHeadersFactory;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Psr\Container\ContainerInterface;

class PreviewCacheHeadersFactoryTest extends TestCase
{
    public function testFactory() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with(ApiClient::class)
            ->willReturn($this->createMock(ApiClient::class));

        $factory = new PreviewCacheHeadersFactory();
        $factory($container);
        $this->addToAssertionCount(1);
    }
}
