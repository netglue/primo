<?php
declare(strict_types=1);

namespace Primo\Router\Container;

use Primo\Router\DocumentResolver;
use Primo\Router\RouteParams;
use Prismic\Api;
use Psr\Container\ContainerInterface;

final class DocumentResolverFactory
{
    public function __invoke(ContainerInterface $container) : DocumentResolver
    {
        return new DocumentResolver(
            $container->get(Api::class),
            $container->get(RouteParams::class)
        );
    }
}
