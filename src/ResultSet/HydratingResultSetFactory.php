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

    /** @var TypeMap */
    private $map;

    public function __construct(TypeMap $map)
    {
        $this->map = $map;
    }

    public function withHttpResponse(ResponseInterface $response) : ResultSet
    {
        return $this->withJsonObject(
            Json::decodeObject((string) $response->getBody())
        );
    }

    public function withJsonObject(object $data) : ResultSet
    {
        $results = [];
        foreach ($data->results as $documentData) {
            $content = DocumentData::factory($documentData);
            $class = $this->map->className($content->type());
            $results[] = new $class($content);
        }

        return new HydratingResultSet(
            self::assertObjectPropertyIsInteger($data, 'page'),
            self::assertObjectPropertyIsInteger($data, 'results_per_page'),
            self::assertObjectPropertyIsInteger($data, 'total_results_size'),
            self::assertObjectPropertyIsInteger($data, 'total_pages'),
            self::optionalStringProperty($data, 'next_page'),
            self::optionalStringProperty($data, 'prev_page'),
            $results
        );
    }
}
