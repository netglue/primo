<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use DateTimeImmutable;
use DateTimeZone;
use Http\Discovery\Psr17FactoryDiscovery;
use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Exception\RequestError;
use Primo\Middleware\DocumentResolver;
use Primo\Router\DocumentResolver as Resolver;
use PrimoTest\Unit\TestCase;
use Prismic\Document;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DocumentResolverTest extends TestCase
{
    private MockObject|Resolver $resolver;
    private RouteResult|MockObject $routeResult;
    private RequestHandlerInterface $handler;
    private ServerRequestInterface $request;
    private MockObject|Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->resolver = $this->createMock(Resolver::class);
        $this->routeResult = $this->createMock(RouteResult::class);
        $this->handler = new class () implements RequestHandlerInterface {
            public ServerRequestInterface $lastRequest;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;

                return new TextResponse('Boom');
            }
        };
        $this->document = $this->createMock(Document::class);
        $this->document
            ->method('lastPublished')
            ->willReturn(DateTimeImmutable::createFromFormat('!Y-m-d', '2020-01-01', new DateTimeZone('UTC')));
    }

    public function testAnExceptionIsThrownWhenThereIsNoRouteResultAvailable(): void
    {
        $subject = new DocumentResolver($this->resolver);
        $this->expectException(RequestError::class);
        $this->expectExceptionMessage('The request for /foo failed because the route result was not available.');
        $subject->process($this->request, $this->handler);
    }

    public function testThatGivenADocumentCanBeResolvedTheDocumentIsInjectedToRequestAttributes(): ResponseInterface
    {
        $this->resolver->method('resolve')->with($this->routeResult)->willReturn(
            $this->document,
        );
        $request = $this->request->withAttribute(RouteResult::class, $this->routeResult);
        self::assertNull($request->getAttribute(Document::class));

        $subject = new DocumentResolver($this->resolver);
        $response = $subject->process($request, $this->handler);
        self::assertSame($this->document, $this->handler->lastRequest->getAttribute(Document::class));

        return $response;
    }

    public function testThatRequestAttributeIsNotPresentWhenADocumentCannotBeResolved(): void
    {
        $this->resolver->method('resolve')->with($this->routeResult)->willReturn(null);

        $request = $this->request->withAttribute(RouteResult::class, $this->routeResult);
        self::assertNull($request->getAttribute(Document::class));
        $subject = new DocumentResolver($this->resolver);
        $subject->process($request, $this->handler);
        self::assertNull($this->handler->lastRequest->getAttribute(Document::class));
    }
}
