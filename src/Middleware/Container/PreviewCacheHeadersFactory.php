<?php

declare(strict_types=1);

namespace Primo\Middleware\Container;

use Primo\Middleware\PreviewCacheHeaders;
use Prismic\ApiClient;
use Psr\Container\ContainerInterface;

class PreviewCacheHeadersFactory
{
    public function __invoke(ContainerInterface $container): PreviewCacheHeaders
    {
        return new PreviewCacheHeaders($container->get(ApiClient::class));
    }
}
