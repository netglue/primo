<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Router;

use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use Primo\Exception\ConfigurationError;
use Primo\Router\RouteMatcher;
use Primo\Router\RouteParams;
use PrimoTest\Unit\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteMatcherTest extends TestCase
{
    /** @var RouteParams */
    private $params;
    /** @var RouteCollector */
    private $collector;
    /** @var MiddlewareInterface */
    private $middleware;

    protected function setUp() : void
    {
        parent::setUp();
        $this->params = RouteParams::fromArray([]);
        $router = new FastRouteRouter();
        $this->collector = new RouteCollector($router);

        $this->middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new TextResponse('Hey');
            }
        };
    }

    private function matcher() : RouteMatcher
    {
        return new RouteMatcher($this->params, $this->collector);
    }

    public function testThatBookmarkedRouteIsNullWhenThereAreNoMatchingRoutes() : void
    {
        $matcher = $this->matcher();
        $this->assertNull($matcher->getBookmarkedRoute('anything'));
    }

    public function testThatTypedRouteIsNullWhenThereAreNoMatchingRoutes() : void
    {
        $matcher = $this->matcher();
        $this->assertNull($matcher->getTypedRoute('any-type'));
    }

    public function testThatTheRouteMatcherCanFindABookmarkedRoute() : void
    {
        $bookmarkedRoute = $this->collector->get('/some-path', $this->middleware, 'bookmark-route');
        $bookmarkedRoute->setOptions(['defaults' => [$this->params->bookmark() => 'bookmark-name']]);
        $matcher = $this->matcher();
        $route = $matcher->getBookmarkedRoute('bookmark-name');
        $this->assertSame($bookmarkedRoute, $route);
    }

    public function testThatATypedRouteIsMatchedWhenTypeIsDefinedAsAString() : void
    {
        $typedRoute = $this->collector->get('/some-path', $this->middleware, 'typed-route');
        $typedRoute->setOptions(['defaults' => [$this->params->type() => 'some-type']]);
        $matcher = $this->matcher();
        $route = $matcher->getTypedRoute('some-type');
        $this->assertSame($typedRoute, $route);
    }

    public function testThatATypedRouteIsMatchedWhenTheTypeIsDefinedAsAnArray() : void
    {
        $typedRoute = $this->collector->get('/some-path', $this->middleware, 'typed-route');
        $typedRoute->setOptions([
            'defaults' => [
                $this->params->type() => [
                    'some-type',
                    'other-type',
                ],
            ],
        ]);
        $matcher = $this->matcher();
        $this->assertSame($typedRoute, $matcher->getTypedRoute('some-type'));
        $this->assertSame($typedRoute, $matcher->getTypedRoute('other-type'));
        $this->assertNull($matcher->getTypedRoute('wrong-type'));
    }

    public function testThatMatchingIsFifoByDefault() : void
    {
        $first = $this->collector->get('/some-path', $this->middleware, 'typed-route');
        $first->setOptions(['defaults' => [$this->params->type() => 'type']]);
        $second = $this->collector->get('/other-path', $this->middleware, 'typed-route');
        $second->setOptions(['defaults' => [$this->params->type() => 'type']]);
        $matcher = $this->matcher();
        $this->assertSame($first, $matcher->getTypedRoute('type'));
    }

    public function testThatMultipleRoutesMatchingATypeCanBeFound() : void
    {
        $first = $this->collector->get('/some-path', $this->middleware, 'some-route');
        $first->setOptions(['defaults' => [$this->params->type() => 'type']]);
        $second = $this->collector->get('/other-path', $this->middleware, 'other-route');
        $second->setOptions(['defaults' => [$this->params->type() => 'type']]);
        $third = $this->collector->get('/third-path', $this->middleware, 'third-route');
        $third->setOptions(['defaults' => []]);
        $matcher = $this->matcher();
        $results = $matcher->routesMatchingType('type');
        $this->assertCount(2, $results);
        $this->assertNotContains($third, $results);
    }

    public function testThatMultipleRoutesMatchingATagCanBeFound() : void
    {
        $first = $this->collector->get('/some-path', $this->middleware, 'some-route');
        $first->setOptions(['defaults' => [$this->params->tag() => 'a']]);
        $second = $this->collector->get('/other-path', $this->middleware, 'other-route');
        $second->setOptions(['defaults' => [$this->params->tag() => 'a']]);
        $third = $this->collector->get('/third-path', $this->middleware, 'third-route');
        $third->setOptions(['defaults' => [$this->params->tag() => 'b']]);
        $matcher = $this->matcher();
        $results = $matcher->routesMatchingTag('a');
        $this->assertCount(2, $results);
        $this->assertNotContains($third, $results);
    }

    public function testThatMatchingTagsIsCaseSensitive() : void
    {
        $first = $this->collector->get('/some-path', $this->middleware, 'some-route');
        $first->setOptions(['defaults' => [$this->params->tag() => 'a']]);
        $second = $this->collector->get('/other-path', $this->middleware, 'other-route');
        $second->setOptions(['defaults' => [$this->params->tag() => 'A']]);

        $matcher = $this->matcher();
        $results = $matcher->routesMatchingTag('a');
        $this->assertCount(1, $results);
        $this->assertContains($first, $results);
        $results = $matcher->routesMatchingTag('A');
        $this->assertCount(1, $results);
        $this->assertContains($second, $results);
    }

    public function testThatUidRouteIsNullWhenThereAreNoMatchingRoutes() : void
    {
        $matcher = $this->matcher();
        $this->assertNull($matcher->getUidRoute('foo', 'bar'));
    }

    public function testThatUidRouteIsNullWhenTypeMatchesButUidDoesNot() : void
    {
        $route = $this->collector->get('/some-path', $this->middleware, 'some-route');
        $route->setOptions(['defaults' => [$this->params->type() => 'a']]);
        $matcher = $this->matcher();
        $this->assertNull($matcher->getUidRoute('a', 'bar'));
    }

    public function testThatUidRouteMatches() : void
    {
        $route = $this->collector->get('/some-path', $this->middleware, 'some-route');
        $route->setOptions(['defaults' => [$this->params->type() => 'a', $this->params->uid() => 'bar']]);
        $matcher = $this->matcher();
        $this->assertSame($route, $matcher->getUidRoute('a', 'bar'));
    }

    /** @return mixed[] */
    public function routeMatchingProvider() : array
    {
        return [
            'bookmark' => [$this->params->bookmark() => 'mark'],
            'id' => [$this->params->id() => 'id'],
            'uid' => [
                $this->params->type() => 'type',
                $this->params->uid() => 'uid',
            ],
            'type-only' => [$this->params->type() => 'type'],
            'one-tag' => [
                $this->params->type() => 'type',
                $this->params->tag() => 'tag',
            ],
            'two-tags' => [
                $this->params->type() => 'type',
                $this->params->tag() => ['a', 'b'],
            ],
        ];
    }

    private function loadRoutes() : void
    {
        foreach ($this->routeMatchingProvider() as $name => $defaults) {
            $route = $this->collector->get('/' . $name, $this->middleware, $name);
            $route->setOptions(['defaults' => $defaults]);
        }
    }

    public function testBestMatch() : void
    {
        $this->loadRoutes();
        $matcher = $this->matcher();
        $this->assertBastMatch('bookmark', $matcher->bestMatch('id', 'type', 'no-match', 'mark', ['tag']));
        $this->assertBastMatch('id', $matcher->bestMatch('id', 'no-match', 'no-match', null, ['tag']));
        $this->assertBastMatch('uid', $matcher->bestMatch('id', 'type', 'uid', null, ['tag']));
        $this->assertBastMatch('type-only', $matcher->bestMatch('id', 'type', 'no-match', null, ['no-match']));
        $this->assertBastMatch('one-tag', $matcher->bestMatch('id', 'type', 'no-match', null, ['tag']));
        $this->assertBastMatch('one-tag', $matcher->bestMatch('id', 'type', 'no-match', null, ['tag', 'tag2']));
        $this->assertBastMatch('type-only', $matcher->bestMatch('id', 'type', 'no-match', null, ['a']));
        $this->assertBastMatch('two-tags', $matcher->bestMatch('id', 'type', 'no-match', null, ['a', 'b']));
    }

    private function assertBastMatch(string $name, ?Route $match) : void
    {
        $this->assertNotNull($match, 'A match was not found');
        $this->assertSame($name, $match->getName());
    }

    public function testRouteDefinitionWithInvalidTagParameterWillCauseException() : void
    {
        $route = $this->collector->get('/some-path', $this->middleware, 'some-route');
        $route->setOptions(['defaults' => [$this->params->tag() => true]]);
        $matcher = $this->matcher();
        $this->expectException(ConfigurationError::class);
        $this->expectExceptionMessage('Tags specified in routes must be either a string or an array of strings');
        $matcher->matchesTag($route, 'foo');
    }
}
