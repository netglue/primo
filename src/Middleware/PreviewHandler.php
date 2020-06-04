<?php
declare(strict_types=1);

namespace Primo\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Prismic\Api;
use Prismic\LinkResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function urldecode;

final class PreviewHandler implements MiddlewareInterface
{
    /** @var Api */
    private $api;

    /** @var LinkResolver */
    private $linkResolver;

    /** @var string */
    private $defaultUrl;

    public function __construct(Api $api, LinkResolver $linkResolver, string $defaultUrl = '/')
    {
        $this->api = $api;
        $this->linkResolver = $linkResolver;
        $this->defaultUrl = $defaultUrl;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $query = $request->getQueryParams();
        if (! isset($query['token']) || empty($query['token'])) {
            // Pass through in order to raise a 404
            return $handler->handle($request);
        }

        $token = urldecode($query['token']);

        $link = $this->api->previewSession($token);
        $url = $link ? $this->linkResolver->resolve($link) : $this->defaultUrl;

        return new RedirectResponse($url, 302);
    }
}
