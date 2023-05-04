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

use function assert;
use function is_bool;

/** @psalm-suppress DeprecatedClass, DeprecatedMethod, DeprecatedProperty */
final class LinkResolver implements PrismicLinkResolver
{
    /**
     * @deprecated $bookmarks
     *
     * @param Bookmark[] $bookmarks
     */
    public function __construct(
        private RouteParams $routeParams,
        private RouteMatcher $routeMatcher,
        private UrlHelper $urlHelper,
        private iterable $bookmarks,
    ) {
    }

    public function resolve(Link $link): string|null
    {
        if ($link instanceof UrlLink) {
            return $link->url();
        }

        if ($link instanceof DocumentLink) {
            return $this->resolveDocumentLink($link);
        }

        return null;
    }

    private function resolveDocumentLink(DocumentLink $link): string|null
    {
        if ($link->isBroken()) {
            return null;
        }

        $route = $this->routeMatcher->bestMatch(
            $link->id(),
            $link->type(),
            $link->uid(),
            $this->bookmarkNameByDocumentId($link->id()),
            $link->tags(),
        );
        if ($route) {
            return $this->url($link, $route);
        }

        return null;
    }

    /** @return non-empty-array<string, string|null> */
    private function routeParams(DocumentLink $link): array
    {
        /**
         * You cannot use tags to construct an url from scratch because there is no way of knowing which
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

    /** @deprecated */
    private function bookmarkNameByDocumentId(string $id): string|null
    {
        foreach ($this->bookmarks as $bookmark) {
            if ($bookmark->documentId() === $id) {
                return $bookmark->name();
            }
        }

        return null;
    }

    private function url(DocumentLink $link, Route $route): string
    {
        $options = $route->getOptions();
        $reuseResultParams = $options['defaults'][$this->routeParams->reuseResultParams()] ?? true;
        assert(is_bool($reuseResultParams));

        return $this->urlHelper->generate(
            $route->getName(),
            $this->routeParams($link),
            [],
            null,
            ['reuse_result_params' => $reuseResultParams],
        );
    }
}
