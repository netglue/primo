<?php

declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\RouteResult;
use Primo\Exception\RoutingError;
use Prismic\Document;

interface RoutingDocumentResolver
{
    /**
     * @throws RoutingError if the uid is required by the route, but no document type is defined.
     * @throws RoutingError if the matched route will yield more than one document.
     */
    public function resolve(RouteResult $routeResult): Document|null;
}
