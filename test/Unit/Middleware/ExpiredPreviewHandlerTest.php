<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use Laminas\Diactoros\Response\TextResponse;
use Primo\Middleware\ExpiredPreviewHandler;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Prismic\Exception\PreviewTokenExpired;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExpiredPreviewHandlerTest extends TestCase
{
    /** @var ExpiredPreviewHandler */
    private $subject;
    /** @var ServerRequestInterface */
    private $request;
    /** @var RequestHandlerInterface */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->handler = new class () implements RequestHandlerInterface {
            /** @var ServerRequestInterface */
            public $lastRequest;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;

                return new TextResponse('Boom');
            }
        };
        $this->subject = new ExpiredPreviewHandler('/go-here');
    }

    public function testThatMiddlewareIsNoOpByDefault(): void
    {
        $response = $this->subject->process($this->request, $this->handler);
        self::assertSame('Boom', (string) $response->getBody());
    }

    public function testThatResponseIsRedirectWithCookieWhenExpiryErrorIsPresent(): void
    {
        $error = new PreviewTokenExpired('Bad News');
        $request = $this->request->withAttribute(PreviewTokenExpired::class, $error);
        $response = $this->subject->process($request, $this->handler);

        self::assertResponseHasStatus($response, 302);
        self::assertMessageHasHeader($response, 'Set-Cookie', self::stringStartsWith(ApiClient::PREVIEW_COOKIE));
    }
}
