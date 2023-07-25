<?php

declare(strict_types=1);

namespace Primo\Middleware;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Laminas\Diactoros\Response\RedirectResponse;
use Prismic\ApiClient;
use Prismic\Exception\PreviewTokenExpired;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ExpiredPreviewHandler implements MiddlewareInterface
{
    public function __construct(private string $redirectUrl = '/')
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $request->getAttribute(PreviewTokenExpired::class)) {
            return $handler->handle($request);
        }

        $response = new RedirectResponse($this->redirectUrl);

        return FigResponseCookies::set($response, SetCookie::create(ApiClient::PREVIEW_COOKIE, '')->expire());
    }
}
