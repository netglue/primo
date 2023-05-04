<?php

declare(strict_types=1);

namespace Primo;

use GSteel\Dot;
use Mezzio\Application;
use Primo\Middleware\ExpiredPreviewHandler;
use Primo\Middleware\PreviewHandler;
use Primo\Middleware\WebhookHandler;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class RouteProvider
{
    public const PREVIEW_ROUTE_NAME = 'prismic-preview-route';
    public const WEBHOOK_ROUTE_NAME = 'prismic-webhook-route';

    public function __invoke(Application $application, ContainerInterface $container): void
    {
        $this->configurePreviews($container, $application);
        $this->configureWebhooks($container, $application);
    }

    private function configurePreviews(ContainerInterface $container, Application $app): void
    {
        $config = $container->has('config') ? $container->get('config') : [];
        Assert::isArray($config);

        $previewUrl = Dot::stringDefault(
            'primo.previews.previewUrl',
            $config,
            ConfigProvider::DEFAULT_PREVIEW_URL,
        );
        Assert::stringNotEmpty($previewUrl);

        $app->get($previewUrl, [
            PreviewHandler::class,
            ExpiredPreviewHandler::class,
        ], self::PREVIEW_ROUTE_NAME);
    }

    private function configureWebhooks(ContainerInterface $container, Application $app): void
    {
        $config = $container->has('config') ? $container->get('config') : [];
        Assert::isArray($config);

        $enabled = Dot::boolDefault('primo.webhook.enabled', $config, false);
        if (! $enabled) {
            return;
        }

        $url = Dot::stringDefault('primo.webhook.url', $config, ConfigProvider::DEFAULT_WEBHOOK_URL);
        Assert::stringNotEmpty($url);
        $app->post($url, WebhookHandler::class, self::WEBHOOK_ROUTE_NAME);
    }
}
