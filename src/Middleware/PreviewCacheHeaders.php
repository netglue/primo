<?php

declare(strict_types=1);

namespace Primo\Middleware;

use Prismic\ApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PreviewCacheHeaders implements MiddlewareInterface
{
    /** @var ApiClient */
    private $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response->getStatusCode() === 200 && $this->apiClient->inPreview()) {
            $response = $response->withHeader('Cache-Control', 'no-cache');
        }

        return $response;
    }
}
