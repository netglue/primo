<?php
declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;

use function array_reverse;
use function in_array;
use function is_array;
use function is_string;

final class RouteMatcher
{
    /** @var RouteParams */
    private $params;
    /** @var RouteCollector */
    private $collector;
    /** @var bool */
    private $lifo;

    public function __construct(RouteParams $params, RouteCollector $collector, bool $lifo = false)
    {
        $this->params = $params;
        $this->collector = $collector;
        $this->lifo = $lifo;
    }

    public function getBookmarkedRoute(string $bookmark) :? Route
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
    public function routesMatchingType(string $type) : iterable
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
    public function routesMatchingTag(string $tag) : iterable
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

    public function getTypedRoute(string $type) :? Route
    {
        foreach ($this->routes() as $route) {
            if ($this->matchesType($route, $type)) {
                return $route;
            }
        }

        return null;
    }

    public function getUidRoute(string $type, string $uid) :? Route
    {
        foreach ($this->routesMatchingType($type) as $route) {
            if ($this->matchesUid($route, $uid)) {
                return $route;
            }
        }

        return null;
    }

    private function matchesType(Route $route, string $type) : bool
    {
        $options = $route->getOptions();
        $option = $options['defaults'][$this->params->type()] ?? [];
        if (is_array($option) && in_array($type, $option, true)) {
            return true;
        }

        return is_string($option) && $option === $type;
    }

    public function matchesTag(Route $route, string $tag) : bool
    {
        $options = $route->getOptions();
        $option = $options['defaults'][$this->params->tag()] ?? null;
        $match = is_string($option) ? $option : null;

        return $match === $tag;
    }

    private function matchesUid(Route $route, string $uid) : bool
    {
        $options = $route->getOptions();
        $option = $options['defaults'][$this->params->uid()] ?? null;

        return $option === $uid;
    }

    /** @return Route[] */
    private function routes() : iterable
    {
        $routes = $this->collector->getRoutes();

        return $this->lifo
            ? array_reverse($routes)
            : $routes;
    }
}
