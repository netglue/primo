<?php

declare(strict_types=1);

namespace Primo\Middleware\Container;

use GSteel\Dot;
use Primo\Middleware\PreviewHandler;
use Prismic\ApiClient;
use Prismic\LinkResolver;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class PreviewHandlerFactory
{
    public function __invoke(ContainerInterface $container): PreviewHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];
        Assert::isArray($config);
        $defaultUrl = Dot::stringDefault('primo.previews.defaultUrl', $config, '/');

        return new PreviewHandler(
            $container->get(ApiClient::class),
            $container->get(LinkResolver::class),
            $defaultUrl,
        );
    }
}
