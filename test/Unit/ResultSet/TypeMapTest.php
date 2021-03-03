<?php

declare(strict_types=1);

namespace PrimoTest\Unit\ResultSet;

use Primo\Content\Document;
use Primo\Exception\InvalidArgument;
use Primo\ResultSet\TypeMap;
use Primo\UnknownClass;
use PrimoTest\Unit\Asset\BadHierarchy;
use PrimoTest\Unit\Asset\SimpleDocument;
use PrimoTest\Unit\TestCase;

class TypeMapTest extends TestCase
{
    public function testGivenAnyTypeTheDefaultWillBeReturned(): void
    {
        $map = new TypeMap([]);
        self::assertSame(Document::class, $map->className('whatever'));
    }

    public function testGivenSpecificTypeTheCorrectClassWillBeReturned(): void
    {
        $map = new TypeMap([
            SimpleDocument::class => ['a', 'b'],
        ]);

        self::assertSame(SimpleDocument::class, $map->className('a'));
        self::assertSame(SimpleDocument::class, $map->className('b'));
    }

    public function testThatMappingCanBeString(): void
    {
        $map = new TypeMap([SimpleDocument::class => 'c']);
        self::assertSame(SimpleDocument::class, $map->className('c'));
    }

    public function testThatAnExceptionIsThrownWhenAClassDoesNotExist(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('The target class "Primo\UnknownClass" does not exist. Please create it or check your document type mapping configuration.');

        new TypeMap([
            UnknownClass::class => ['a', 'b'],
        ]);
    }

    public function testThatAnExceptionIsThrownWithClassNamesNotImplementingHierarchy(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('All target classes to hydrate to must descend from ' . Document::class);

        new TypeMap([
            BadHierarchy::class => ['a', 'b'],
        ]);
    }
}
