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
use Traversable;

use function array_filter;
use function count;
use function iterator_to_array;
use function reset;

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

        $route = $this->resolveByUid($link);
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
        $routes = $this->routeMatcher->routesMatchingType($link->type());
        $routes = $routes instanceof Traversable ? iterator_to_array($routes, false) : $routes;
        if (count($routes) === 1) {
            return reset($routes);
        }

        // Can the matches based on type be reduced to a single match based on document tags?
        $matches = array_filter($routes, function (Route $route) use ($link) : bool {
            foreach ($link->tags() as $tag) {
                if ($this->routeMatcher->matchesTag($route, $tag)) {
                    return true;
                }
            }

            return false;
        });

        if (count($matches) === 1) {
            return reset($matches);
        }

        return null;
    }

    private function resolveByUid(DocumentLink $link) :? Route
    {
        return $this->routeMatcher->getUidRoute($link->type(), $link->uid());
    }

    /** @return string[] */
    private function routeParams(DocumentLink $link) : array
    {
        /**
         * You cannot use tags to construct a url from scratch because there is no way of knowing which
         * tag amongst many is the correct tag for the current context.
         */
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
