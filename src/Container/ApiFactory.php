<?php

declare(strict_types=1);

namespace Primo\Container;

use Primo\Cache\PrismicApiCache;
use Primo\Exception\ConfigurationError;
use Primo\Http\PrismicHttpClient;
use Prismic\Api;
use Prismic\ResultSet\ResultSetFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

use function assert;
use function is_array;
use function is_string;

final class ApiFactory
{
    public function __invoke(ContainerInterface $container): Api
    {
        $config = $container->has('config') ? $container->get('config') : [];
        assert(is_array($config));
        $config = isset($config['prismic']) && is_array($config['prismic']) ? $config['prismic'] : [];

        $apiUrl = $config['api'] ?? null;
        if (empty($apiUrl) || ! is_string($apiUrl)) {
            throw new ConfigurationError(
                'An api url cannot be determined. Your content repository url should be available in ' .
                'configuration under [prismic][api] and should be a non-empty string.'
            );
        }

        $accessToken = $config['token'] ?? null;
        assert(is_string($accessToken) || $accessToken === null);

        return Api::get(
            $apiUrl,
            $accessToken,
            $container->has(PrismicHttpClient::class) ? $container->get(PrismicHttpClient::class) : null,
            $container->has(RequestFactoryInterface::class) ? $container->get(RequestFactoryInterface::class) : null,
            $container->has(UriFactoryInterface::class) ? $container->get(UriFactoryInterface::class) : null,
            $container->has(ResultSetFactory::class) ? $container->get(ResultSetFactory::class) : null,
            $container->has(PrismicApiCache::class) ? $container->get(PrismicApiCache::class) : null
        );
    }
}
