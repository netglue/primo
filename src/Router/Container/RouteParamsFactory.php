<?php

declare(strict_types=1);

namespace Primo\Router\Container;

use GSteel\Dot;
use Primo\Router\RouteParams;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class RouteParamsFactory
{
    public function __invoke(ContainerInterface $container): RouteParams
    {
        $config = $container->has('config')
            ? $container->get('config')
            : [];
        Assert::isArray($config);

        $options = Dot::arrayDefault(
            'primo.router.params',
            $config,
            [],
        );

        Assert::isMap($options);
        Assert::allString($options);

        return RouteParams::fromArray($options);
    }
}
