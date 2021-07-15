<?php

declare(strict_types=1);

namespace PrimoTest\Integration;

use Laminas;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ServiceManager\ServiceManager;
use Mezzio;
use PHPUnit\Framework\TestCase;
use Primo\ConfigProvider;
use Psr\Container\ContainerInterface;

use function assert;

abstract class IntegrationTestCase extends TestCase
{
    /**
     * Returns application config as it would likely appear in a Mezzio Application
     *
     * @return array<array-key, mixed>
     */
    protected function getApplicationConfig(): array
    {
        $aggregator = new ConfigAggregator([
            Mezzio\ConfigProvider::class,
            Mezzio\Helper\ConfigProvider::class,
            Mezzio\Router\ConfigProvider::class,
            Mezzio\Router\FastRouteRouter\ConfigProvider::class,
            Laminas\Diactoros\ConfigProvider::class,
            ConfigProvider::class,
        ]);

        return $aggregator->getMergedConfig();
    }

    /**
     * Returns a fresh container configured with application config as you'd expect for a Mezzio application
     *
     * Pass in custom application configuration if required.
     *
     * @param array<array-key, mixed>|null $withCustomConfiguration
     */
    protected function getContainer(?array $withCustomConfiguration = null): ContainerInterface
    {
        $config = $withCustomConfiguration ?: $this->getApplicationConfig();
        assert(isset($config['dependencies']));
        $dependencies = $config['dependencies'];
        $dependencies['services']['config'] = $config;

        return new ServiceManager($dependencies);
    }

    protected function getApplication(): Mezzio\Application
    {
        return $this->getContainer()->get(Mezzio\Application::class);
    }
}
