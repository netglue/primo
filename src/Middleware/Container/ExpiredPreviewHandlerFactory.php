<?php
declare(strict_types=1);

namespace Primo\Middleware\Container;

use Primo\Middleware\ExpiredPreviewHandler;
use Psr\Container\ContainerInterface;

class ExpiredPreviewHandlerFactory
{
    public function __invoke(ContainerInterface $container) : ExpiredPreviewHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new ExpiredPreviewHandler($config['primo']['previews']['defaultUrl'] ?? '/');
    }
}
