<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Router\Container;

use Mezzio\Router\RouteCollector;
use Primo\Router\Container\RouteMatcherFactory;
use Primo\Router\RouteParams;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class RouteMatcherFactoryTest extends TestCase
{
    public function testFactory() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                [RouteParams::class, RouteParams::fromArray([])],
                [RouteCollector::class, $this->createMock(RouteCollector::class)],
            ]);
        $factory = new RouteMatcherFactory();
        $factory->__invoke($container);
    }
}
