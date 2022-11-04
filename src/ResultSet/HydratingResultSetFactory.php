<?php

declare(strict_types=1);

namespace Primo\ResultSet;

use Prismic\Json;
use Prismic\ResultSet;
use Prismic\ResultSet\ResultSetFactory;
use Prismic\Value\DataAssertionBehaviour;
use Prismic\Value\DocumentData;
use Psr\Http\Message\ResponseInterface;

final class HydratingResultSetFactory implements ResultSetFactory
{
    use DataAssertionBehaviour;

    public function __construct(private TypeMap $map)
    {
    }

    public function withHttpResponse(ResponseInterface $response): ResultSet
    {
        return $this->withJsonObject(
            Json::decodeObject((string) $response->getBody()),
        );
    }

    public function withJsonObject(object $object): ResultSet
    {
        $results = [];
        foreach ($object->results as $documentData) {
            $content = DocumentData::factory($documentData);
            $class = $this->map->className($content->type());
            $results[] = new $class($content);
        }

        return new HydratingResultSet(
            self::assertObjectPropertyIsInteger($object, 'page'),
            self::assertObjectPropertyIsInteger($object, 'results_per_page'),
            self::assertObjectPropertyIsInteger($object, 'total_results_size'),
            self::assertObjectPropertyIsInteger($object, 'total_pages'),
            self::optionalStringProperty($object, 'next_page'),
            self::optionalStringProperty($object, 'prev_page'),
            $results,
        );
    }
}
