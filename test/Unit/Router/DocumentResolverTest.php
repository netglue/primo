<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Router;

use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Exception\RoutingError;
use Primo\Router\DocumentResolver;
use Primo\Router\RouteParams;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Prismic\Document;
use Prismic\Query;
use Prismic\ResultSet;
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
        self::assertNull($this->resolver->resolve($result));
    }

    public function testThatResultFromApiWillBeReturnedWhenBookmarkMatches() : void
    {
        $document = $this->createMock(Document::class);
        $this->api->expects(self::once())
            ->method('findByBookmark')
            ->with('bookmark-name')
            ->willReturn($document);

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [$this->params->bookmark() => 'bookmark-name']
        );

        self::assertSame($document, $this->resolver->resolve($result));
    }

    public function testThatResultFromApiWillBeReturnedWhenRouteMatchesDocumentId() : void
    {
        $document = $this->createMock(Document::class);
        $this->api->expects(self::once())
            ->method('findById')
            ->with('doc-id')
            ->willReturn($document);

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [$this->params->id() => 'doc-id']
        );

        self::assertSame($document, $this->resolver->resolve($result));
    }

    private function apiWillReturnSingleDocumentInLanguage(string $lang) : Document
    {
        $document = $this->createMock(Document::class);
        $query = $this->createMock(Query::class);
        $resultSet = $this->createMock(ResultSet::class);

        $query->expects(self::once())
            ->method('query')
            ->willReturnSelf();
        $query->expects(self::once())
            ->method('lang')
            ->with($lang)
            ->willReturnSelf();

        $this->api->expects(self::once())
            ->method('createQuery')
            ->willReturn($query);

        $this->api->expects(self::once())
            ->method('query')
            ->with($query)
            ->willReturn($resultSet);

        $resultSet->expects(self::once())
            ->method('count')
            ->willReturn(1);

        $resultSet->expects(self::once())
            ->method('first')
            ->willReturn($document);

        return $document;
    }

    public function testThatResultFromApiIsReturnedWhenRouteMatchesTypeAndUid() : void
    {
        $document = $this->apiWillReturnSingleDocumentInLanguage('*');

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [
                $this->params->uid() => 'uid',
                $this->params->type() => 'type',
            ]
        );

        self::assertSame($document, $this->resolver->resolve($result));
    }

    public function testThatLanguageIsProvidedToApiMethodWhenFoundInTheRouteParams() : void
    {
        $document = $this->apiWillReturnSingleDocumentInLanguage('en-gb');

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET']),
            [
                $this->params->uid() => 'uid',
                $this->params->type() => 'type',
                $this->params->lang() => 'en-gb',
            ]
        );

        self::assertSame($document, $this->resolver->resolve($result));
    }

    public function testThatTypeMustBeKnownInOrderToResolveByUid() : void
    {
        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET'], 'myRoute'),
            [$this->params->uid() => 'uid']
        );

        $this->expectException(RoutingError::class);
        $this->expectExceptionMessage('The route named "myRoute" matches a Prismic UID, but the type cannot be resolved');
        $this->resolver->resolve($result);
    }

    public function testThatItIsPossibleToQueryOnASingleType() : void
    {
        $document = $this->apiWillReturnSingleDocumentInLanguage('*');

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET'], 'myRoute'),
            [$this->params->type() => 'type']
        );

        self::assertSame($document, $this->resolver->resolve($result));
    }

    public function testThatItIsPossibleToQueryByTag() : void
    {
        $document = $this->apiWillReturnSingleDocumentInLanguage('*');

        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET'], 'myRoute'),
            [$this->params->tag() => 'my-tag']
        );

        self::assertSame($document, $this->resolver->resolve($result));
    }

    public function testAnExceptionIsThrownWhenAResultSetContainsMultipleResults() : void
    {
        $query = $this->createMock(Query::class);
        $resultSet = $this->createMock(ResultSet::class);

        $query->expects(self::once())
            ->method('query')
            ->willReturnSelf();
        $query->expects(self::once())
            ->method('lang')
            ->with('*')
            ->willReturnSelf();

        $this->api->expects(self::once())
            ->method('createQuery')
            ->willReturn($query);

        $this->api->expects(self::once())
            ->method('query')
            ->with($query)
            ->willReturn($resultSet);

        $resultSet->method('count')
            ->willReturn(2);

        $this->expectException(RoutingError::class);
        $this->expectExceptionMessage('The route named "myRoute" matched 2 documents when transformed into a query');
        $result = RouteResult::fromRoute(
            new Route('/foo', $this->middleware, ['GET'], 'myRoute'),
            [$this->params->tag() => 'my-tag']
        );
        $this->resolver->resolve($result);
    }
}
