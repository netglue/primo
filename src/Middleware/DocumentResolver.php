<?php

declare(strict_types=1);

namespace Primo\Middleware;

use Mezzio\Router\RouteResult;
use Primo\Exception\RequestError;
use Primo\Router\DocumentResolver as Resolver;
use Prismic\Document;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DocumentResolver implements MiddlewareInterface
{
    /** @var Resolver */
    private $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
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
