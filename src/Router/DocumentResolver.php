<?php

declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\RouteResult;
use Primo\Exception\RoutingError;
use Prismic\ApiClient;
use Prismic\Document;
use Prismic\Predicate;

use function assert;
use function count;
use function is_string;
use function sprintf;

/** @psalm-suppress DeprecatedMethod */
class DocumentResolver
{
    public function __construct(
        private ApiClient $api,
        private RouteParams $routeParams,
    ) {
    }

    /**
     * @throws RoutingError if the uid is required by the route, but no document type is defined.
     * @throws RoutingError if the matched route will yield more than one document.
     */
    public function resolve(RouteResult $routeResult): Document|null
    {
        $document = $this->resolveWithBookmark($routeResult);

        if (! $document) {
            $document = $this->resolveWithParams($routeResult);
        }

        if (! $document) {
            $document = $this->resolveWithId($routeResult);
        }

        return $document;
    }

    /** @deprecated */
    private function resolveWithBookmark(RouteResult $routeResult): Document|null
    {
        $params = $routeResult->getMatchedParams();
        $bookmark = $params[$this->routeParams->bookmark()] ?? null;
        if ($bookmark === null) {
            return null;
        }

        return $this->api->findByBookmark($bookmark);
    }

    /** @throws RoutingError */
    private function resolveWithParams(RouteResult $routeResult): Document|null
    {
        $params = $routeResult->getMatchedParams();
        $type = $params[$this->routeParams->type()] ?? null;
        $uid = $params[$this->routeParams->uid()]  ?? null;
        $id = $params[$this->routeParams->id()] ?? null;
        $tags = $params[$this->routeParams->tag()] ?? null;

        assert(is_string($type) || $type === null);
        assert(is_string($uid) || $uid === null);
        assert(is_string($id) || $id === null);

        // At least one of these must be present to attempt a match
        if ($type === null && $uid === null && $tags === null) {
            return null;
        }

        // If the uid is present, the type must be present
        if ($uid !== null && $type === null) {
            throw RoutingError::uidMatchedWithoutType($routeResult);
        }

        $predicates = [];
        if ($type !== null) {
            $predicates[] = Predicate::at('document.type', $type);
        }

        if ($uid !== null) {
            $predicates[] = Predicate::at(sprintf('my.%s.uid', $type), $uid);
        }

        if ($id !== null) {
            $predicates[] = Predicate::at('document.id', $id);
        }

        if ($tags !== null && $tags !== [] && $tags !== '') {
            $tags = is_string($tags) ? [$tags] : $tags;
            $predicates[] = Predicate::at('document.tags', $tags);
        }

        $lang = $params[$this->routeParams->lang()] ?? '*';
        assert(is_string($lang));

        $query = $this->api->createQuery()
            ->query(...$predicates)
            ->lang($lang);

        $resultSet = $this->api->query($query);
        if (count($resultSet) > 1) {
            throw RoutingError::nonUniqueResult($routeResult, $resultSet);
        }

        return $resultSet->first();
    }

    private function resolveWithId(RouteResult $routeResult): Document|null
    {
        $params = $routeResult->getMatchedParams();
        $id = $params[$this->routeParams->id()] ?? null;
        if (! is_string($id) || $id === '') {
            return null;
        }

        return $this->api->findById($id);
    }
}
