<?php
declare(strict_types=1);

namespace Primo\Middleware\Container;

use Mezzio\Template\TemplateRendererInterface;
use Primo\Middleware\PrismicTemplateHandler;
use Psr\Container\ContainerInterface;

final class PrismicTemplateHandlerFactory
{
    public function __invoke(ContainerInterface $container) : PrismicTemplateHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $templateAttribute = $config['primo']['templates']['templateAttribute']
            ?? PrismicTemplateHandler::DEFAULT_TEMPLATE_ATTRIBUTE;

        return new PrismicTemplateHandler(
            $container->get(TemplateRendererInterface::class),
            $templateAttribute
        );
    }
}
