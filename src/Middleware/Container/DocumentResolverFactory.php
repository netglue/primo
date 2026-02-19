<?php

declare(strict_types=1);

namespace Primo\Middleware\Container;

use Primo\Middleware\DocumentResolver;
use Primo\Router\RoutingDocumentResolver;
use Psr\Container\ContainerInterface;

final class DocumentResolverFactory
{
    public function __invoke(ContainerInterface $container): DocumentResolver
    {
        return new DocumentResolver(
            $container->get(RoutingDocumentResolver::class),
        );
    }
}
