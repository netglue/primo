<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Primo\Middleware\Container\InjectRequestCookiesFactory;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Psr\Container\ContainerInterface;

class InjectRequestCookiesFactoryTest extends TestCase
{
    public function testFactory() : void
    {
        $api = $this->createMock(ApiClient::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with(ApiClient::class)
            ->willReturn($api);

        $factory = new InjectRequestCookiesFactory();
        $factory->__invoke($container);
    }
}
