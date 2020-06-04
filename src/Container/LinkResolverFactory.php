<?php
declare(strict_types=1);

namespace Primo\Container;

use Mezzio\Helper\UrlHelper;
use Primo\LinkResolver;
use Primo\Router\RouteMatcher;
use Primo\Router\RouteParams;
use Prismic\Api;
use Psr\Container\ContainerInterface;

final class LinkResolverFactory
{
    public function __invoke(ContainerInterface $container) : LinkResolver
    {
        return new LinkResolver(
            $container->get(RouteParams::class),
            $container->get(RouteMatcher::class),
            $container->get(UrlHelper::class),
            $container->get(Api::class)->data()->bookmarks()
        );
    }
}
