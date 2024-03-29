<?php

declare(strict_types=1);

namespace Primo\ResultSet;

use Prismic\Document;
use Prismic\ResultSet;
use Prismic\ResultSet\TypicalResultSetBehaviour;

use function array_merge;
use function count;
use function max;

/** @template-implements ResultSet<Document> */
final class HydratingResultSet implements ResultSet
{
    /** @use TypicalResultSetBehaviour<Document> */
    use TypicalResultSetBehaviour;

    /** @param list<Document> $results */
    public function __construct(
        int $page,
        int $resultsPerPage,
        int $totalResults,
        int $pageCount,
        string|null $nextPage,
        string|null $previousPage,
        array $results,
    ) {
        $this->page = $page;
        $this->perPage = $resultsPerPage;
        $this->totalResults = $totalResults;
        $this->pageCount = $pageCount;
        $this->nextPage = $nextPage;
        $this->prevPage = $previousPage;
        $this->results = $results;
    }

    /**
     * @param ResultSet<Document> $with
     *
     * @return ResultSet<Document>
     */
    public function merge(ResultSet $with): ResultSet
    {
        $results = array_merge($this->results, $with->results());

        return new self(
            1,
            count($results),
            $this->totalResults,
            max($this->pageCount - 1, 1),
            $with->nextPage(),
            $this->prevPage,
            $results,
        );
    }
}
