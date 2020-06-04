<?php
declare(strict_types=1);

namespace Primo\Container;

use Prismic\ResultSet\ResultSetFactory;
use Prismic\ResultSet\StandardResultSetFactory;
use Psr\Container\ContainerInterface;

final class StandardResultSetFactoryFactory
{
    public function __invoke(ContainerInterface $container) : ResultSetFactory
    {
        return new StandardResultSetFactory();
    }
}
