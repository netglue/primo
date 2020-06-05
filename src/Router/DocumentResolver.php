<?php
declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\RouteResult;
use Prismic\ApiClient;
use Prismic\Document;

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

    public function resolve(RouteResult $routeResult) :? Document
    {
        $document = $this->resolveWithBookmark($routeResult);

        if (! $document) {
            $document = $this->resolveWithUid($routeResult);
        }

        if (! $document) {
            $document = $this->resolveWithId($routeResult);
        }

        return $document;
    }

    private function resolveWithBookmark(RouteResult $routeResult) :? Document
    {
        $params = $routeResult->getMatchedParams();
        $bookmark = $params[$this->routeParams->bookmark()] ?? null;
        if (! $bookmark) {
            return null;
        }

        return $this->api->findByBookmark($bookmark);
    }

    private function resolveWithUid(RouteResult $routeResult) :? Document
    {
        $params = $routeResult->getMatchedParams();
        $type = $params[$this->routeParams->type()] ?? null;
        $uid  = $params[$this->routeParams->uid()]  ?? null;
        $lang = $params[$this->routeParams->lang()] ?? '*';
        if (! $type || ! $uid) {
            return null;
        }

        return $this->api->findByUid($type, $uid, $lang);
    }

    private function resolveWithId(RouteResult $routeResult) :? Document
    {
        $params = $routeResult->getMatchedParams();
        $id = $params[$this->routeParams->id()] ?? null;
        if (! $id) {
            return null;
        }

        return $this->api->findById($id);
    }
}
