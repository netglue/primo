<?php
declare(strict_types=1);

namespace PrimoTest\Unit\ResultSet\Container;

use Primo\ResultSet\Container\HydratingResultSetFactoryFactory;
use Primo\ResultSet\TypeMap;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class HydratingResultSetFactoryFactoryTest extends TestCase
{
    public function testFactory() : void
    {
        $map = new TypeMap([]);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with(TypeMap::class)
            ->willReturn($map);

        $factory = new HydratingResultSetFactoryFactory();
        $factory->__invoke($container);
    }
}
