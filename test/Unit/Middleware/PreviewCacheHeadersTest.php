<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Middleware\PreviewCacheHeaders;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PreviewCacheHeadersTest extends TestCase
{
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private MockObject|ApiClient $api;
    private PreviewCacheHeaders $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new TextResponse('Boom');
            }
        };
        $this->api = $this->createMock(ApiClient::class);
        $this->subject = new PreviewCacheHeaders($this->api);
    }

    public function testThatNoCacheControlHeaderIsSetWhenPreviewIsNotActive(): void
    {
        $this->api->method('inPreview')->willReturn(false);
        $response = $this->subject->process($this->request, $this->handler);

        self::assertEmpty($response->getHeader('Cache-Control'));
    }

    public function testThatCacheControlHeaderIsSetWhenPreviewIsActive(): void
    {
        $this->api->method('inPreview')->willReturn(true);
        $response = $this->subject->process($this->request, $this->handler);

        self::assertMessageHasHeader($response, 'Cache-Control');
    }
}
