<?php

declare(strict_types=1);

namespace Primo\Router\Container;

use Primo\Router\DocumentResolver;
use Primo\Router\RouteParams;
use Prismic\ApiClient;
use Psr\Container\ContainerInterface;

final class DocumentResolverFactory
{
    public function __invoke(ContainerInterface $container): DocumentResolver
    {
        return new DocumentResolver(
            $container->get(ApiClient::class),
            $container->get(RouteParams::class)
        );
    }
}
