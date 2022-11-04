<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Middleware\PreviewHandler;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Exception\InvalidPreviewToken;
use Prismic\Exception\PreviewTokenExpired;
use Prismic\LinkResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PreviewHandlerTest extends TestCase
{
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private MockObject|ApiClient $api;
    private MockObject|LinkResolver $linkResolver;
    private PreviewHandler $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->handler = new class () implements RequestHandlerInterface {
            public ServerRequestInterface $lastRequest;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;

                return new TextResponse('Boom');
            }
        };
        $this->api = $this->createMock(ApiClient::class);
        $this->linkResolver = $this->createMock(LinkResolver::class);
        $this->subject = new PreviewHandler($this->api, $this->linkResolver, '/go-here');
    }

    public function testThatWhenTheTokenIsEmptyNoRedirectWillOccur(): void
    {
        self::assertEmpty($this->request->getQueryParams());
        $response = $this->subject->process($this->request, $this->handler);
        self::assertSame('Boom', (string) $response->getBody());
    }

    public function testThatWhenTheTokenIsInvalidNoRedirectWillOccur(): void
    {
        $token = 'expected-token';
        $request = $this->request->withQueryParams(['token' => $token]);
        $this->api
            ->expects(self::once())
            ->method('previewSession')
            ->with($token)
            ->willThrowException(new InvalidPreviewToken('bad news'));
        $response = $this->subject->process($request, $this->handler);
        self::assertSame('Boom', (string) $response->getBody());
    }

    public function testThatWhenTheTokenHasExpiredNoRedirectOccursAndRequestAttributeIsGiven(): void
    {
        $token = 'expected-token';
        $request = $this->request->withQueryParams(['token' => $token]);
        $error = new PreviewTokenExpired('bad news');
        $this->api
            ->expects(self::once())
            ->method('previewSession')
            ->with($token)
            ->willThrowException($error);

        $response = $this->subject->process($request, $this->handler);
        self::assertSame('Boom', (string) $response->getBody());

        self::assertSame($error, $this->handler->lastRequest->getAttribute(PreviewTokenExpired::class));
    }

    public function testThatTheRedirectWillBeTheDefaultUrlWhenTheApiDoesNotReturnALink(): void
    {
        $token = 'expected-token';
        $request = $this->request->withQueryParams(['token' => $token]);

        $this->api->method('previewSession')->with($token)->willReturn(null);
        $response = $this->subject->process($request, $this->handler);
        self::assertResponseHasStatus($response, 302);
        self::assertMessageHasHeader($response, 'location', '/go-here');
    }

    public function testThatTheRedirectWillBeDeterminedByLinkResolver(): void
    {
        $token = 'expected-token';
        $request = $this->request->withQueryParams(['token' => $token]);

        $link = DocumentLink::new('a', 'b', 'c', 'd', false);
        $this->api->method('previewSession')->with($token)->willReturn($link);
        $this->linkResolver->method('resolve')->with($link)->willReturn('/go-there');
        $response = $this->subject->process($request, $this->handler);
        self::assertResponseHasStatus($response, 302);
        self::assertMessageHasHeader($response, 'location', '/go-there');
    }
}
