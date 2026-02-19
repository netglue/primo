<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Exception\RequestError;
use Primo\Middleware\PrismicTemplate;
use PrimoTest\Unit\TestCase;
use Prismic\Document;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AllowMockObjectsWithoutExpectations]
final class PrismicTemplateTest extends TestCase
{
    private TemplateRendererInterface&MockObject $templates;
    private PrismicTemplate $subject;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->templates = $this->createMock(TemplateRendererInterface::class);
        $this->subject = new PrismicTemplate($this->templates);
        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->handler = new class () implements RequestHandlerInterface {
            public ServerRequestInterface|null $lastRequest = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;

                return new TextResponse('Boom');
            }
        };
    }

    public function testThatTheTemplateMustBeKnownInAdvance(): void
    {
        $this->expectException(RequestError::class);
        $this->expectExceptionMessage('The request for "/foo" failed because there was no template attribute found in the request');
        $this->subject->process($this->request, $this->handler);
    }

    private function requestHasTemplate(): ServerRequestInterface
    {
        return $this->request->withAttribute('template', 'template::foo');
    }

    public function testThatRequestIsDelegatedWhenADocumentIsNotFound(): void
    {
        $request = $this->requestHasTemplate();
        self::assertNull($this->handler->lastRequest);

        $this->subject->process($request, $this->handler);

        self::assertSame($request, $this->handler->lastRequest);
    }

    private function requestHasDocument(Document $document): ServerRequestInterface
    {
        $request = $this->requestHasTemplate();

        return $request->withAttribute(Document::class, $document);
    }

    public function testThatTheTemplateWillBeRenderedWhenRequestCriteriaAreMet(): void
    {
        $document = $this->createMock(Document::class);
        $document->expects(self::once())
            ->method('lang')
            ->willReturn('en-gb');

        $this->templates->expects(self::once())
            ->method('addDefaultParam')
            ->with(
                self::equalTo(TemplateRendererInterface::TEMPLATE_ALL),
                self::equalTo('document'),
                self::equalTo($document),
            );

        $this->templates->expects(self::once())
            ->method('render')
            ->with(self::equalTo('template::foo'))
            ->willReturn('Some Markup');

        $response = $this->subject->process($this->requestHasDocument($document), $this->handler);
        self::assertResponseIsSuccess($response);
        self::assertMessageBodyMatches($response, self::equalTo('Some Markup'));
        self::assertMessageHasHeader($response, 'content-language', self::equalTo('en-gb'));
    }
}
