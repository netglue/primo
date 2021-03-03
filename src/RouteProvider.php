<?php

declare(strict_types=1);

namespace Primo;

use Mezzio\Application;
use Primo\Middleware\ExpiredPreviewHandler;
use Primo\Middleware\PreviewHandler;
use Primo\Middleware\WebhookHandler;
use Psr\Container\ContainerInterface;

final class RouteProvider
{
    public function __invoke(Application $application, ContainerInterface $container): void
    {
        $this->configurePreviews($container, $application);
        $this->configureWebhooks($container, $application);
    }

    private function configurePreviews(ContainerInterface $container, Application $app): void
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $previewUrl = $config['primo']['previews']['previewUrl'] ?? ConfigProvider::DEFAULT_PREVIEW_URL;
        $app->get($previewUrl, [
            PreviewHandler::class,
            ExpiredPreviewHandler::class,
        ]);
    }

    private function configureWebhooks(ContainerInterface $container, Application $app): void
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $options = $config['primo']['webhook'];
        $enabled = $options['enabled'] ?? false;
        if (! $enabled) {
            return;
        }

        $url = $options['url'] ?? ConfigProvider::DEFAULT_WEBHOOK_URL;
        $app->post($url, WebhookHandler::class);
    }
}
