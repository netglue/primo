<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Container;

use Primo\Container\StandardResultSetFactoryFactory;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class StandardResultSetFactoryFactoryTest extends TestCase
{
    public function testFactory() : void
    {
        $factory = new StandardResultSetFactoryFactory();
        $factory->__invoke($this->createMock(ContainerInterface::class));
        $this->addToAssertionCount(1);
    }
}
