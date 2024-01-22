<?php

declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use Primo\Exception\ConfigurationError;

use function array_diff;
use function count;
use function end;
use function in_array;
use function is_array;
use function is_string;
use function uasort;

/** @psalm-suppress DeprecatedMethod */
final class RouteMatcher
{
    public function __construct(
        private RouteParams $params,
        private RouteCollector $collector,
    ) {
    }

    /**
     * Return the route with the best match for the given parameters
     *
     * @param string[] $tags
     */
    public function bestMatch(string $id, string $type, string|null $uid, string|null $bookmark, array $tags): Route|null
    {
        // A matching bookmark is nearly the most specific type of route and the easiest to reason about
        if ($bookmark !== null) {
            $route = $this->getBookmarkedRoute($bookmark);
            if ($route) {
                return $route;
            }
        }

        // See if there's a route with a hard-coded UID that matches the argument
        if ($uid !== null) {
            $route = $this->getUidRoute($type, $uid);
            if ($route) {
                return $route;
            }
        }

        /** @var ScoredRoute[] $candidates */
        $candidates = [];
        foreach ($this->routesMatchingType($type) as $route) {
            $score = 0;
            // Increase score by the number of tags desired by the route where all tags match
            $routeTags = $this->wantsTag($route);
            $unmatched = count(array_diff($routeTags, $tags));
            if ($unmatched > 0) {
                continue;
            }

            $score += count($routeTags);
            $candidates[] = new ScoredRoute($route, $score);
        }

        uasort($candidates, static function (ScoredRoute $a, ScoredRoute $b): int {
            return $a->compare($b);
        });

        if (count($candidates)) {
            $best = end($candidates);

            return $best->route();
        }

        // Finally, if for some reason an identifier has been hard-coded into route definitions, try returning a match for that
        return $this->routeMatchingId($id);
    }

    /** @deprecated */
    public function getBookmarkedRoute(string $bookmark): Route|null
    {
        foreach ($this->routes() as $route) {
            $options = $route->getOptions();
            $option = $options['defaults'][$this->params->bookmark()] ?? null;

            if ($option === $bookmark) {
                return $route;
            }
        }

        return null;
    }

    /** @return Route[] */
    public function routesMatchingType(string $type): iterable
    {
        $routes = [];
        foreach ($this->routes() as $route) {
            if (! $this->matchesType($route, $type)) {
                continue;
            }

            $routes[] = $route;
        }

        return $routes;
    }

    /** @return Route[] */
    public function routesMatchingTag(string $tag): iterable
    {
        $routes = [];
        foreach ($this->routes() as $route) {
            if (! $this->matchesTag($route, $tag)) {
                continue;
            }

            $routes[] = $route;
        }

        return $routes;
    }

    public function getTypedRoute(string $type): Route|null
    {
        foreach ($this->routes() as $route) {
            if ($this->matchesType($route, $type)) {
                return $route;
            }
        }

        return null;
    }

    public function getUidRoute(string $type, string $uid): Route|null
    {
        foreach ($this->routesMatchingType($type) as $route) {
            if ($this->matchesUid($route, $uid)) {
                return $route;
            }
        }

        return null;
    }

    private function matchesType(Route $route, string $type): bool
    {
        $options = $route->getOptions();
        $option = $options['defaults'][$this->params->type()] ?? [];
        if (is_array($option) && in_array($type, $option, true)) {
            return true;
        }

        return is_string($option) && $option === $type;
    }

    public function matchesTag(Route $route, string $tag): bool
    {
        return in_array($tag, $this->wantsTag($route), true);
    }

    /** @return string[] */
    private function wantsTag(Route $route): array
    {
        $options = $route->getOptions();
        $tags = $options['defaults'][$this->params->tag()] ?? null;
        if ($tags === null) {
            return [];
        }

        if (is_string($tags)) {
            return [$tags];
        }

        if (! is_array($tags)) {
            throw new ConfigurationError(
                'Tags specified in routes must be either a string or an array of strings',
            );
        }

        return $tags;
    }

    private function matchesUid(Route $route, string $uid): bool
    {
        $options = $route->getOptions();
        $option = $options['defaults'][$this->params->uid()] ?? null;

        return $option === $uid;
    }

    private function routeMatchingId(string $id): Route|null
    {
        foreach ($this->routes() as $route) {
            $options = $route->getOptions();
            $option = $options['defaults'][$this->params->id()] ?? null;
            if ($option === $id) {
                return $route;
            }
        }

        return null;
    }

    /** @return Route[] */
    private function routes(): iterable
    {
        return $this->collector->getRoutes();
    }
}
