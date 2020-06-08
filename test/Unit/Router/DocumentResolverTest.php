<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Router;

use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Router\DocumentResolver;
use Primo\Router\RouteParams;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Prismic\Document;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DocumentResolverTest extends TestCase
{
    /** @var MockObject|ApiClient */
    private $api;
    /** @var DocumentResolver */
    private $resolver;
    /** @var MiddlewareInterface */
    private $middleware;
    /** @var RouteParams */
    private $params;

    protected function setUp() : void
    {
        parent::setUp();
        $this->api = $this->createMock(ApiClient::class);
        $this->params = RouteParams::fromArray([]);
        $this->resolver = new DocumentResolver($this->api, $this->params);
        $this->middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new TextResponse('Hey');
            }
        };
    }

    public function testRouteResultWithoutMatchingParamsWillReturnNull() : void
    {
        $result = RouteResult::fromRoute(new Route('/foo', $this->middleware, ['GET']), []);
        $this->assertNull($this->resolver->resolve($result));
    }

    public function testThatResultFromApiWillBeReturnedWhenBookmarkMatches() : void
    {
        $document = $this->createMock(Document::class);
        $this->api->expects($this->once())
            ->method('findByBookmark')
            ->with('bookmark-name')
            ->willReturn($document);

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [$this->params->bookmark() => 'bookmark-name']
        );

        $this->assertSame($document, $this->resolver->resolve($result));
    }

    public function testThatResultFromApiWillBeReturnedWhenRouteMatchesDocumentId() : void
    {
        $document = $this->createMock(Document::class);
        $this->api->expects($this->once())
            ->method('findById')
            ->with('doc-id')
            ->willReturn($document);

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [$this->params->id() => 'doc-id']
        );

        $this->assertSame($document, $this->resolver->resolve($result));
    }

    public function testThatResultFromApiIsReturnedWhenRouteMatchesType() : void
    {
        $document = $this->createMock(Document::class);
        $this->api->expects($this->once())
            ->method('findByUid')
            ->with('type', 'uid', '*')
            ->willReturn($document);

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [
                $this->params->uid() => 'uid',
                $this->params->type() => 'type',
            ]
        );

        $this->assertSame($document, $this->resolver->resolve($result));
    }

    public function testThatLanguageIsProvidedToApiMethodWhenFoundInTheRouteParams() : void
    {
        $document = $this->createMock(Document::class);
        $this->api->expects($this->once())
            ->method('findByUid')
            ->with('type', 'uid', 'en-gb')
            ->willReturn($document);

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [
                $this->params->uid() => 'uid',
                $this->params->type() => 'type',
                $this->params->lang() => 'en-gb',
            ]
        );

        $this->assertSame($document, $this->resolver->resolve($result));
    }
}
