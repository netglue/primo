<?php

declare(strict_types=1);

namespace Primo\Container;

use Mezzio\Helper\UrlHelper;
use Primo\LinkResolver;
use Primo\Router\RouteMatcher;
use Primo\Router\RouteParams;
use Prismic\ApiClient;
use Psr\Container\ContainerInterface;

final class LinkResolverFactory
{
    /** @psalm-suppress DeprecatedClass, DeprecatedMethod */
    public function __invoke(ContainerInterface $container): LinkResolver
    {
        return new LinkResolver(
            $container->get(RouteParams::class),
            $container->get(RouteMatcher::class),
            $container->get(UrlHelper::class),
            $container->get(ApiClient::class)->data()->bookmarks()
        );
    }
}
