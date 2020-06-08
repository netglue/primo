<?php
declare(strict_types=1);

namespace Primo\ResultSet;

use Primo\Content\Document;
use Primo\Exception\InvalidArgument;

use function is_a;
use function is_array;
use function sprintf;

final class TypeMap
{
    /** @var string[] */
    private $map;

    /** @var string */
    private $default;

    /**
     * Map Prismic Types to Classes
     *
     * The array of mapping information should use the FQCN as the key and either a string, or an array of strings referring
     * to a document type in your repository.
     *
     * @param string[] $map
     */
    public function __construct(iterable $map, string $defaultDocumentType = Document::class)
    {
        $this->map = [];
        $this->classHierarchyCheck($defaultDocumentType);
        $this->default = $defaultDocumentType;

        foreach ($map as $class => $type) {
            $target = is_array($type) ? $type : [$type];
            $this->addTypes($class, $target);
        }
    }

    public function className(string $type) : string
    {
        return $this->map[$type] ?? $this->default;
    }

    /** @param string[] $types */
    private function addTypes(string $className, array $types) : void
    {
        foreach ($types as $type) {
            $this->addType($type, $className);
        }
    }

    private function addType(string $type, string $class) : void
    {
        $this->classHierarchyCheck($class);
        $this->map[$type] = $class;
    }

    private function classHierarchyCheck(string $className) : void
    {
        if (! is_a($className, Document::class, true)) {
            throw new InvalidArgument(sprintf(
                'All target classes to hydrate to must descend from %s because I can guarantee the constructor accepts ' .
                'a DocumentData value object. If you want to opt out of this hierarchy, you need to make your own hydrating ' .
                'result set',
                Document::class
            ));
        }
    }
}
