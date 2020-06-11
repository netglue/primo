<?php
declare(strict_types=1);

namespace Primo\Middleware\Container;

use Mezzio\Template\TemplateRendererInterface;
use Primo\Middleware\PrismicTemplate;
use Psr\Container\ContainerInterface;

final class PrismicTemplateFactory
{
    public function __invoke(ContainerInterface $container) : PrismicTemplate
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $templateAttribute = $config['primo']['templates']['templateAttribute']
            ?? PrismicTemplate::DEFAULT_TEMPLATE_ATTRIBUTE;

        return new PrismicTemplate(
            $container->get(TemplateRendererInterface::class),
            $templateAttribute
        );
    }
}
