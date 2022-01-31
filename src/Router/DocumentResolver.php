<?php

declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\RouteResult;
use Primo\Exception\RoutingError;
use Prismic\ApiClient;
use Prismic\Document;
use Prismic\Predicate;

use function count;
use function is_string;
use function sprintf;

class DocumentResolver
{
    /** @var RouteParams */
    private $routeParams;
    /** @var ApiClient */
    private $api;

    public function __construct(ApiClient $api, RouteParams $routeParams)
    {
        $this->api = $api;
        $this->routeParams = $routeParams;
    }

    /**
     * @throws RoutingError if the uid is required by the route, but no document type is defined.
     * @throws RoutingError if the matched route will yield more than one document.
     */
    public function resolve(RouteResult $routeResult): ?Document
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

    /**
     * @psalm-suppress DeprecatedMethod
     */
    private function resolveWithBookmark(RouteResult $routeResult): ?Document
    {
        $params = $routeResult->getMatchedParams();
        $bookmark = $params[$this->routeParams->bookmark()] ?? null;
        if (! $bookmark) {
            return null;
        }

        return $this->api->findByBookmark($bookmark);
    }

    /** @throws RoutingError */
    private function resolveWithParams(RouteResult $routeResult): ?Document
    {
        $params = $routeResult->getMatchedParams();
        $type = $params[$this->routeParams->type()] ?? null;
        $uid = $params[$this->routeParams->uid()]  ?? null;
        $id = $params[$this->routeParams->id()] ?? null;
        $tags = $params[$this->routeParams->tag()] ?? null;

        // At least one of these must be present to attempt a match
        if (! $type && ! $uid && ! $tags) {
            return null;
        }

        // If the uid is present, the type must be present
        if ($uid && ! $type) {
            throw RoutingError::uidMatchedWithoutType($routeResult);
        }

        $predicates = [];
        if ($type) {
            $predicates[] = Predicate::at('document.type', $type);
        }

        if ($uid) {
            $predicates[] = Predicate::at(sprintf('my.%s.uid', $type), $uid);
        }

        if ($id) {
            $predicates[] = Predicate::at('document.id', $id);
        }

        if (! empty($tags)) {
            $tags = is_string($tags) ? [$tags] : $tags;
            $predicates[] = Predicate::at('document.tags', $tags);
        }

        $lang = $params[$this->routeParams->lang()] ?? '*';

        $query = $this->api->createQuery()
            ->query(...$predicates)
            ->lang($lang);

        $resultSet = $this->api->query($query);
        if (count($resultSet) > 1) {
            throw RoutingError::nonUniqueResult($routeResult, $resultSet);
        }

        return $resultSet->first();
    }

    private function resolveWithId(RouteResult $routeResult): ?Document
    {
        $params = $routeResult->getMatchedParams();
        $id = $params[$this->routeParams->id()] ?? null;
        if (! $id) {
            return null;
        }

        return $this->api->findById($id);
    }
}
