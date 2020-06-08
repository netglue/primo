<?php
declare(strict_types=1);

namespace Primo\ResultSet\Container;

use Primo\Content\Document;
use Primo\ResultSet\TypeMap;
use Psr\Container\ContainerInterface;

final class TypeMapFactory
{
    public function __invoke(ContainerInterface $container) : TypeMap
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $options = $config['primo']['typeMap'] ?? [];

        return new TypeMap(
            $options['map'] ?? [],
            $options['default'] ?? Document::class
        );
    }
}
