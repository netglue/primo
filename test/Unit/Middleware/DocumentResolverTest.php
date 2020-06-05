<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

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
    /** @var MockObject|Resolver */
    private $resolver;
    /** @var RouteResult|MockObject */
    private $routeResult;
    /** @var RequestHandlerInterface */
    private $handler;
    /** @var ServerRequestInterface */
    private $request;

    protected function setUp() : void
    {
        parent::setUp();
        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->resolver = $this->createMock(Resolver::class);
        $this->routeResult = $this->createMock(RouteResult::class);
        $this->handler = new class () implements RequestHandlerInterface {
            /** @var ServerRequestInterface */
            public $lastRequest;

            public function handle(ServerRequestInterface $request) : ResponseInterface
            {
                $this->lastRequest = $request;

                return new TextResponse('Boom');
            }
        };
    }

    public function testAnExceptionIsThrownWhenThereIsNoRouteResultAvailable() : void
    {
        $subject = new DocumentResolver($this->resolver);
        $this->expectException(RequestError::class);
        $this->expectDeprecationMessage('The request for /foo failed because the route result was not available.');
        $subject->process($this->request, $this->handler);
    }

    public function testThatGivenADocumentCanBeResolvedTheDocumentIsInjectedToRequestAttributes() : void
    {
        $document = $this->createMock(Document::class);
        $this->resolver->method('resolve')->with($this->routeResult)->willReturn(
            $document
        );
        $request = $this->request->withAttribute(RouteResult::class, $this->routeResult);
        $this->assertNull($request->getAttribute(Document::class));

        $subject = new DocumentResolver($this->resolver);
        $subject->process($request, $this->handler);
        $this->assertSame($document, $this->handler->lastRequest->getAttribute(Document::class));
    }

    public function testThatRequestAttributeIsNotPresentWhenADocumentCannotBeResolved() : void
    {
        $this->resolver->method('resolve')->with($this->routeResult)->willReturn(null);

        $request = $this->request->withAttribute(RouteResult::class, $this->routeResult);
        $this->assertNull($request->getAttribute(Document::class));
        $subject = new DocumentResolver($this->resolver);
        $subject->process($request, $this->handler);
        $this->assertNull($this->handler->lastRequest->getAttribute(Document::class));
    }
}
