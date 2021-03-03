<?php

declare(strict_types=1);

namespace PrimoTest\Unit\ResultSet\Container;

use Primo\Content\Document;
use Primo\ResultSet\Container\TypeMapFactory;
use PrimoTest\Unit\Asset\SimpleDocument;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class TypeMapFactoryTest extends TestCase
{
    public function testFactoryWithConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn([
                'primo' => [
                    'typeMap' => [
                        'map' => [
                            SimpleDocument::class => ['a', 'b'],
                            Document::class => 'c',
                        ],
                        'default' => SimpleDocument::class,
                    ],
                ],
            ]);

        $factory = new TypeMapFactory();
        $map = $factory->__invoke($container);

        self::assertSame(SimpleDocument::class, $map->className('a'));
        self::assertSame(SimpleDocument::class, $map->className('b'));
        self::assertSame(Document::class, $map->className('c'));
        self::assertSame(SimpleDocument::class, $map->className('d'));
    }

    public function testFactoryWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(false);
        $container->expects(self::never())
            ->method('get');

        $factory = new TypeMapFactory();
        $map = $factory->__invoke($container);

        self::assertSame(Document::class, $map->className('a'));
        self::assertSame(Document::class, $map->className('b'));
        self::assertSame(Document::class, $map->className('c'));
        self::assertSame(Document::class, $map->className('d'));
    }
}
