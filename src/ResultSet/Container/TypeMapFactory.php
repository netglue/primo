<?php

declare(strict_types=1);

namespace Primo\ResultSet\Container;

use GSteel\Dot;
use Primo\Content\Document;
use Primo\ResultSet\TypeMap;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class TypeMapFactory
{
    public function __invoke(ContainerInterface $container): TypeMap
    {
        $config = $container->has('config') ? $container->get('config') : [];
        Assert::isArray($config);
        $options = Dot::arrayDefault('primo.typeMap', $config, []);

        return new TypeMap(
            $options['map'] ?? [],
            $options['default'] ?? Document::class,
        );
    }
}
