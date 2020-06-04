<?php
declare(strict_types=1);

namespace Primo\Router\Container;

use Primo\Router\RouteParams;
use Psr\Container\ContainerInterface;

final class RouteParamsFactory
{
    public function __invoke(ContainerInterface $container) : RouteParams
    {
        $config = $container->has('config')
            ? $container->get('config')
            : [];

        $options = $config['primo']['router']['params'] ?? [];

        return RouteParams::fromArray($options);
    }
}
