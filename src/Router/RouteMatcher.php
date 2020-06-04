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

    public function getTypedRoute(string $type) :? Route
    {
        foreach ($this->routes() as $route) {
            if ($this->matchesType($route, $type)) {
                return $route;
            }
        }

        return null;
    }

    private function matchesType(Route $route, string $type) : bool
    {
        $options = $route->getOptions();
        $option = $options['defaults'][$this->params->type()] ?? [];
        if (! $option) {
            return false;
        }

        if (is_array($option) && in_array($type, $option, true)) {
            return true;
        }

        return is_string($option) && $option === $type;
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
