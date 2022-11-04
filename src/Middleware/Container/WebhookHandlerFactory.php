<?php

declare(strict_types=1);

namespace Primo\Middleware\Container;

use Primo\Exception\ConfigurationError;
use Primo\Middleware\WebhookHandler;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

use function sprintf;

final class WebhookHandlerFactory
{
    public function __invoke(ContainerInterface $container): WebhookHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new WebhookHandler(
            $this->assertEventDispatcher($container),
            $config['primo']['webhook']['secret'] ?? null,
        );
    }

    private function assertEventDispatcher(ContainerInterface $container): EventDispatcherInterface
    {
        if ($container->has(EventDispatcherInterface::class)) {
            return $container->get(EventDispatcherInterface::class);
        }

        throw new ConfigurationError(sprintf(
            'I cannot retrieve an event dispatcher from the container. I expect to be able to retrieve one with ' .
            'the id "%s". If you already have an event dispatcher that implements PSR-14, alias it to the interface in ' .
            'the container, or install an event dispatcher from ' .
            'https://packagist.org/providers/psr/event-dispatcher-implementation',
            EventDispatcherInterface::class,
        ));
    }
}
