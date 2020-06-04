<?php
declare(strict_types=1);

namespace Primo\Middleware;

use Prismic\Api;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class InjectRequestCookies implements MiddlewareInterface
{
    /** @var Api */
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->api->setRequestCookies($request->getCookieParams());

        return $handler->handle($request);
    }
}
