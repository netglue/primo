<?php

declare(strict_types=1);

namespace Primo\ResultSet\Container;

use Primo\ResultSet\HydratingResultSetFactory;
use Primo\ResultSet\TypeMap;
use Psr\Container\ContainerInterface;

final class HydratingResultSetFactoryFactory
{
    public function __invoke(ContainerInterface $container): HydratingResultSetFactory
    {
        return new HydratingResultSetFactory($container->get(TypeMap::class));
    }
}
