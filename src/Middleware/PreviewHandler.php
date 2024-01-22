<?php

declare(strict_types=1);

namespace Primo\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Prismic\ApiClient;
use Prismic\Exception\InvalidPreviewToken;
use Prismic\Exception\PreviewTokenExpired;
use Prismic\LinkResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function urldecode;

final class PreviewHandler implements MiddlewareInterface
{
    public function __construct(
        private ApiClient $api,
        private LinkResolver $linkResolver,
        private string $defaultUrl = '/',
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $query = $request->getQueryParams();
        if (! isset($query['token']) || empty($query['token'])) {
            // Pass through in order to raise a 404
            return $handler->handle($request);
        }

        $token = urldecode($query['token']);

        try {
            $link = $this->api->previewSession($token);
        } catch (InvalidPreviewToken) {
            return $handler->handle($request);
        } catch (PreviewTokenExpired $expired) {
            return $handler->handle(
                $request->withAttribute(PreviewTokenExpired::class, $expired),
            );
        }

        $resolved = $link ? $this->linkResolver->resolve($link) : null;

        return new RedirectResponse($resolved ?? $this->defaultUrl, 302);
    }
}
