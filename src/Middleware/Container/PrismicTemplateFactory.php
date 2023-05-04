<?php

declare(strict_types=1);

namespace Primo\Middleware\Container;

use GSteel\Dot;
use Mezzio\Template\TemplateRendererInterface;
use Primo\Middleware\PrismicTemplate;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class PrismicTemplateFactory
{
    public function __invoke(ContainerInterface $container): PrismicTemplate
    {
        $config = $container->has('config') ? $container->get('config') : [];
        Assert::isArray($config);
        $templateAttribute = Dot::stringDefault(
            'primo.templates.templateAttribute',
            $config,
            PrismicTemplate::DEFAULT_TEMPLATE_ATTRIBUTE,
        );

        return new PrismicTemplate(
            $container->get(TemplateRendererInterface::class),
            $templateAttribute,
        );
    }
}
