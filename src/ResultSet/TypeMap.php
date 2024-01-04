<?php

declare(strict_types=1);

namespace Primo\ResultSet;

use Primo\Content\Document;
use Primo\Exception\InvalidArgument;

use function class_exists;
use function is_a;
use function is_array;
use function sprintf;

/** @psalm-type Map = array<class-string, list<non-empty-string>|non-empty-string> */
final class TypeMap
{
    /** @var array<non-empty-string, class-string<Document>> */
    private array $map;

    /**
     * Map Prismic Types to Classes
     *
     * The array of mapping information should use the FQCN as the key and either a string, or an array of strings referring
     * to a document type in your repository.
     *
     * @param Map                    $map
     * @param class-string<Document> $defaultDocumentType
     */
    public function __construct(iterable $map, private string $defaultDocumentType = Document::class)
    {
        $this->map = [];
        $this->classHierarchyCheck($defaultDocumentType);

        foreach ($map as $class => $type) {
            $target = is_array($type) ? $type : [$type];
            $this->addTypes($class, $target);
        }
    }

    /**
     * @param non-empty-string $type
     *
     * @return class-string<Document>
     */
    public function className(string $type): string
    {
        return $this->map[$type] ?? $this->defaultDocumentType;
    }

    /**
     * @param class-string           $className
     * @param list<non-empty-string> $types
     */
    private function addTypes(string $className, array $types): void
    {
        foreach ($types as $type) {
            $this->addType($type, $className);
        }
    }

    /**
     * @param non-empty-string $type
     * @param class-string     $class
     */
    private function addType(string $type, string $class): void
    {
        $this->classHierarchyCheck($class);
        $this->map[$type] = $class;
    }

    /**
     * @param class-string $className
     *
     * @psalm-assert class-string<Document> $className
     */
    private function classHierarchyCheck(string $className): void
    {
        if (! class_exists($className)) {
            throw new InvalidArgument(sprintf(
                'The target class "%s" does not exist. Please create it or check your document type mapping configuration.',
                $className,
            ));
        }

        if (! is_a($className, Document::class, true)) {
            throw new InvalidArgument(sprintf(
                'All target classes to hydrate to must descend from %s because I can guarantee the constructor accepts ' .
                'a DocumentData value object. If you want to opt out of this hierarchy, you need to make your own hydrating ' .
                'result set',
                Document::class,
            ));
        }
    }
}
