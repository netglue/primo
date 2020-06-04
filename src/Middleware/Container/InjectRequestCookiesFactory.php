<?php
declare(strict_types=1);

namespace Primo\Middleware\Container;

use Primo\Middleware\InjectRequestCookies;
use Prismic\Api;
use Psr\Container\ContainerInterface;

final class InjectRequestCookiesFactory
{
    public function __invoke(ContainerInterface $container) : InjectRequestCookies
    {
        return new InjectRequestCookies($container->get(Api::class));
    }
}
