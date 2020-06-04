<?php
declare(strict_types=1);

namespace Primo;

use Mezzio\Helper\UrlHelper;
use Mezzio\Router\Route;
use Primo\Router\RouteMatcher;
use Primo\Router\RouteParams;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Link;
use Prismic\LinkResolver as PrismicLinkResolver;
use Prismic\UrlLink;
use Prismic\Value\Bookmark;

final class LinkResolver implements PrismicLinkResolver
{
    /** @var RouteParams */
    private $routeParams;
    /** @var Bookmark[] */
    private $bookmarks;
    /** @var RouteMatcher */
    private $routeMatcher;
    /** @var UrlHelper */
    private $urlHelper;

    /** @param Bookmark[] $bookmarks */
    public function __construct(RouteParams $routeParams, RouteMatcher $matcher, UrlHelper $urlHelper, iterable $bookmarks)
    {
        $this->routeParams = $routeParams;
        $this->bookmarks = $bookmarks;
        $this->routeMatcher = $matcher;
        $this->urlHelper = $urlHelper;
    }

    public function resolve(Link $link) :? string
    {
        if ($link instanceof UrlLink) {
            return $link->url();
        }

        if ($link instanceof DocumentLink) {
            return $this->resolveDocumentLink($link);
        }

        return null;
    }

    private function resolveDocumentLink(DocumentLink $link) :? string
    {
        if ($link->isBroken()) {
            return null;
        }

        $route = $this->resolveAsBookmark($link);
        if ($route) {
            return $this->url($link, $route);
        }

        $route = $this->resolveByType($link);
        if ($route) {
            return $this->url($link, $route);
        }

        return null;
    }

    private function resolveAsBookmark(DocumentLink $link) :? Route
    {
        $bookmark = $this->bookmarkNameByDocumentId($link->id());
        if (! $bookmark) {
            return null;
        }

        return $this->routeMatcher->getBookmarkedRoute($bookmark);
    }

    private function resolveByType(DocumentLink $link) :? Route
    {
        return $this->routeMatcher->getTypedRoute($link->type());
    }

    /** @return string[] */
    private function routeParams(DocumentLink $link) : array
    {
        return [
            $this->routeParams->id() => $link->id(),
            $this->routeParams->uid() => $link->uid(),
            $this->routeParams->type() => $link->type(),
            $this->routeParams->lang() => $link->language(),
            $this->routeParams->bookmark() => $this->bookmarkNameByDocumentId($link->id()),
        ];
    }

    private function bookmarkNameByDocumentId(string $id) :? string
    {
        foreach ($this->bookmarks as $bookmark) {
            if ($bookmark->documentId() === $id) {
                return $bookmark->name();
            }
        }

        return null;
    }

    private function url(DocumentLink $link, Route $route) : string
    {
        $options = $route->getOptions();
        $reuseResultParams = $options['defaults'][$this->routeParams->reuseResultParams()] ?? true;

        return $this->urlHelper->generate(
            $route->getName(),
            $this->routeParams($link),
            [],
            null,
            ['reuse_result_params' => $reuseResultParams]
        );
    }
}
