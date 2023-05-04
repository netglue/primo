<?php

declare(strict_types=1);

namespace Primo\Middleware\Container;

use GSteel\Dot;
use Primo\Middleware\ExpiredPreviewHandler;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ExpiredPreviewHandlerFactory
{
    public function __invoke(ContainerInterface $container): ExpiredPreviewHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];
        Assert::isArray($config);
        $url = Dot::stringDefault('primo.previews.defaultUrl', $config, '/');

        return new ExpiredPreviewHandler($url);
    }
}
