<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Mock\Client;
use Laminas\Diactoros\Response\TextResponse;
use Primo\Middleware\InjectRequestCookies;
use PrimoTest\Unit\TestCase;
use Prismic\Api;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InjectRequestCookiesTest extends TestCase
{
    private Api $api;
    private InjectRequestCookies $subject;
    private RequestHandlerInterface $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = Api::get('https://www.example.com', null, new Client());
        $this->subject = new InjectRequestCookies($this->api);
        $this->handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new TextResponse('Hey!');
            }
        };
    }

    public function testThatRequestCookiesAreProvidedToTheApi(): void
    {
        self::assertFalse($this->api->inPreview());
        $request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $request = $request->withCookieParams([Api::PREVIEW_COOKIE => 'cookieValue']);
        $this->subject->process($request, $this->handler);
        self::assertTrue($this->api->inPreview());
        self::assertSame('cookieValue', (string) $this->api->ref());
    }
}
