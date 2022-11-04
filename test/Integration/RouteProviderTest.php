<?php

declare(strict_types=1);

namespace PrimoTest\Integration;

use Mezzio\Application;
use Mezzio\Router\Route;
use Primo\RouteProvider;

use function array_map;
use function sprintf;

class RouteProviderTest extends IntegrationTestCase
{
    public function testThatTheApplicationDoesNotHaveTheWebhookRouteByDefault(): void
    {
        $application = $this->getApplication();
        self::assertRouteNameNotFound(RouteProvider::WEBHOOK_ROUTE_NAME, $application);
    }

    public function testThatTheWebhookRouteWillNotBeFoundWhenTheRouteProviderIsInvokedWithTheDefaultConfig(): void
    {
        $container = $this->getContainer();
        $application = $container->get(Application::class);
        (new RouteProvider())($application, $container);
        self::assertRouteNameNotFound(RouteProvider::WEBHOOK_ROUTE_NAME, $application);
    }

    public function testThatTheWebhookRouteWillBeAvailableWhenWebhooksHaveBeenEnabled(): void
    {
        $config = $this->getApplicationConfig();
        $config['primo']['webhook']['enabled'] = true;
        $container = $this->getContainer($config);
        $application = $container->get(Application::class);
        (new RouteProvider())($application, $container);
        self::assertRouteNameFound(RouteProvider::WEBHOOK_ROUTE_NAME, $application);
    }

    public function testThatThePreviewRouteWillNotBeConfiguredByDefault(): void
    {
        $application = $this->getApplication();
        self::assertRouteNameNotFound(RouteProvider::PREVIEW_ROUTE_NAME, $application);
    }

    public function testThatThePreviewRouteWillBeConfiguredWhenTheRouteProviderIsInvokedWithTheDefaultConfig(): void
    {
        $container = $this->getContainer();
        $application = $container->get(Application::class);
        (new RouteProvider())($application, $container);
        self::assertRouteNameFound(RouteProvider::PREVIEW_ROUTE_NAME, $application);
    }

    public static function assertRouteNameNotFound(string $routeName, Application $application): void
    {
        $names = array_map(static function (Route $route): string {
            return $route->getName();
        }, $application->getRoutes());

        self::assertNotContains($routeName, $names, sprintf(
            'The route name "%s" should not be present in the configured routes but it was found',
            $routeName,
        ));
    }

    public static function assertRouteNameFound(string $routeName, Application $application): void
    {
        $names = array_map(static function (Route $route): string {
            return $route->getName();
        }, $application->getRoutes());

        self::assertContains($routeName, $names, sprintf(
            'The route name "%s" was not present in the configured routes',
            $routeName,
        ));
    }
}
