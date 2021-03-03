<?php

declare(strict_types=1);

namespace Primo\Middleware\Container;

use Primo\Middleware\PreviewHandler;
use Prismic\ApiClient;
use Prismic\LinkResolver;
use Psr\Container\ContainerInterface;

final class PreviewHandlerFactory
{
    public function __invoke(ContainerInterface $container): PreviewHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $defaultUrl = $config['primo']['previews']['defaultUrl'] ?? '/';

        return new PreviewHandler(
            $container->get(ApiClient::class),
            $container->get(LinkResolver::class),
            $defaultUrl
        );
    }
}
