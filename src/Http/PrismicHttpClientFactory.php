<?php
declare(strict_types=1);

namespace Primo\Http;

use Http\Discovery\Psr18ClientDiscovery;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;

class PrismicHttpClientFactory
{
    public function __invoke(ContainerInterface $container) : ClientInterface
    {
        if ($container->has(ClientInterface::class)) {
            return $container->get(ClientInterface::class);
        }

        return Psr18ClientDiscovery::find();
    }
}
