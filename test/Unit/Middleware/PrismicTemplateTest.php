<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Exception\RequestError;
use Primo\Middleware\PrismicTemplate;
use PrimoTest\Unit\TestCase;
use Prismic\Document;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PrismicTemplateTest extends TestCase
{
    /** @var TemplateRendererInterface|MockObject */
    private $templates;
    /** @var PrismicTemplate */
    private $subject;
    /** @var ServerRequestInterface */
    private $request;
    /** @var MockObject|Document */
    private $document;
    /** @var RequestHandlerInterface */
    private $handler;

    protected function setUp() : void
    {
        parent::setUp();
        $this->templates = $this->createMock(TemplateRendererInterface::class);
        $this->subject = new PrismicTemplate($this->templates);
        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->document = $this->createMock(Document::class);
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

    public function testThatTheTemplateMustBeKnownInAdvance() : void
    {
        $this->expectException(RequestError::class);
        $this->expectExceptionMessage('The request for "/foo" failed because there was no template attribute found in the request');
        $this->subject->process($this->request, $this->handler);
    }

    private function requestHasTemplate() : ServerRequestInterface
    {
        return $this->request->withAttribute('template', 'template::foo');
    }

    public function testThatRequestIsDelegatedWhenADocumentIsNotFound() : void
    {
        $request = $this->requestHasTemplate();
        $this->assertNull($this->handler->lastRequest);

        $this->subject->process($request, $this->handler);

        $this->assertSame($request, $this->handler->lastRequest);
    }

    private function requestHasDocument() : ServerRequestInterface
    {
        $request = $this->requestHasTemplate();

        return $request->withAttribute(Document::class, $this->document);
    }

    public function testThatTheTemplateWillBeRenderedWhenRequestCriteriaAreMet() : void
    {
        $this->templates->expects($this->once())
            ->method('addDefaultParam')
            ->with(
                $this->equalTo(TemplateRendererInterface::TEMPLATE_ALL),
                $this->equalTo('document'),
                $this->equalTo($this->document)
            );

        $this->templates->expects($this->once())
            ->method('render')
            ->with($this->equalTo('template::foo'))
            ->willReturn('Some Markup');

        $this->document->expects($this->once())
            ->method('lang')
            ->willReturn('en-gb');

        $response = $this->subject->process($this->requestHasDocument(), $this->handler);
        self::assertResponseIsSuccess($response);
        self::assertMessageBodyMatches($response, $this->equalTo('Some Markup'));
        self::assertMessageHasHeader($response, 'content-language', 'en-gb');
    }
}
