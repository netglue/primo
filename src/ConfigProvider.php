<?php

declare(strict_types=1);

namespace Primo;

use Primo\Content\Document;
use Prismic;

final class ConfigProvider
{
    public const DEFAULT_PREVIEW_URL = '/preview';
    public const DEFAULT_WEBHOOK_URL = '/prismic-webhook';

    /** @return mixed[] */
    public function __invoke(): array
    {
        return [
            'prismic' => [
                'api' => null,
                'token' => null,
            ],
            'primo' => [
                'router' => $this->routing(),
                'previews' => [
                    // The URL to redirect to when a preview does not specify a document
                    'defaultUrl' => '/',
                    'previewUrl' => self::DEFAULT_PREVIEW_URL,
                ],
                'templates' => [
                    // The attribute to look for in the request that specifies the template to render
                    'templateAttribute' => Middleware\PrismicTemplate::DEFAULT_TEMPLATE_ATTRIBUTE,
                ],
                'webhook' => [
                    // The secret yu expect in the webhook payload
                    'secret' => null,
                    // Webhooks are disabled by default to encourage the configuration of a secret
                    'enabled' => false,
                    // The URL that webhooks should be posted to
                    'url' => self::DEFAULT_WEBHOOK_URL,
                ],
                'typeMap' => [
                    'default' => Document::class,
                    'map' => [],
                ],
            ],
            'dependencies' => $this->dependencies(),
        ];
    }

    /** @return mixed[] */
    private function dependencies(): array
    {
        return [
            'factories' => [
                Http\PrismicHttpClient::class => Http\PrismicHttpClientFactory::class,
                Middleware\DocumentResolver::class => Middleware\Container\DocumentResolverFactory::class,
                Middleware\ExpiredPreviewHandler::class => Middleware\Container\ExpiredPreviewHandlerFactory::class,
                Middleware\InjectRequestCookies::class => Middleware\Container\InjectRequestCookiesFactory::class,
                Middleware\PreviewCacheHeaders::class => Middleware\Container\PreviewCacheHeadersFactory::class,
                Middleware\PreviewHandler::class => Middleware\Container\PreviewHandlerFactory::class,
                Middleware\PrismicTemplate::class => Middleware\Container\PrismicTemplateFactory::class,
                Middleware\WebhookHandler::class => Middleware\Container\WebhookHandlerFactory::class,
                Prismic\ApiClient::class => Container\ApiFactory::class,
                Prismic\LinkResolver::class => Container\LinkResolverFactory::class,
                Prismic\ResultSet\StandardResultSetFactory::class => Container\StandardResultSetFactoryFactory::class,
                ResultSet\HydratingResultSetFactory::class => ResultSet\Container\HydratingResultSetFactoryFactory::class,
                ResultSet\TypeMap::class => ResultSet\Container\TypeMapFactory::class,
                Router\DocumentResolver::class => Router\Container\DocumentResolverFactory::class,
                Router\RouteMatcher::class => Router\Container\RouteMatcherFactory::class,
                Router\RouteParams::class => Router\Container\RouteParamsFactory::class,
            ],
            'aliases' => [
                Prismic\ResultSet\ResultSetFactory::class => Prismic\ResultSet\StandardResultSetFactory::class,
                // To Opt-In to Hydrating Result Sets, alias the Prismic ResultSetFactory to the Hydrating Result Set FQCN
                //Prismic\ResultSet\ResultSetFactory::class => ResultSet\HydratingResultSetFactory::class,
            ],
        ];
    }

    /** @return mixed[] */
    private function routing(): array
    {
        return [
            'params' => [
                'id' => 'document-id',
                'uid' => 'document-uid',
                'type' => 'document-type',
                'bookmark' => 'document-bookmark',
                'lang' => 'document-lang',
                'reuseResultParams' => 'reuse_result_params',
            ],
        ];
    }
}
