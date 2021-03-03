<?php

declare(strict_types=1);

namespace Primo\Router\Container;

use Mezzio\Router\RouteCollector;
use Primo\Router\RouteMatcher;
use Primo\Router\RouteParams;
use Psr\Container\ContainerInterface;

final class RouteMatcherFactory
{
    public function __invoke(ContainerInterface $container): RouteMatcher
    {
        return new RouteMatcher(
            $container->get(RouteParams::class),
            $container->get(RouteCollector::class)
        );
    }
}
