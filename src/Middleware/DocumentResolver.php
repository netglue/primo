<?php

declare(strict_types=1);

namespace Primo\Middleware;

use Mezzio\Router\RouteResult;
use Primo\Exception\RequestError;
use Primo\Router\RoutingDocumentResolver;
use Prismic\Document;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class DocumentResolver implements MiddlewareInterface
{
    public function __construct(private RoutingDocumentResolver $resolver)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get hold of the matched route (RouteResult) so we can inspect and resolve a document
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult instanceof RouteResult) {
            throw RequestError::withMissingRouteResult($request);
        }

        $document = $this->resolver->resolve($routeResult);

        if (! $document) {
            return $handler->handle($request);
        }

        return $handler->handle($request->withAttribute(Document::class, $document));
    }
}
