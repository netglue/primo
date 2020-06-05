<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Exception\DocumentNotFound;
use Primo\Exception\RequestError;
use Primo\Middleware\PrismicTemplateHandler;
use PrimoTest\Unit\TestCase;
use Prismic\Document;
use Psr\Http\Message\ServerRequestInterface;

class PrismicTemplateHandlerTest extends TestCase
{
    /** @var TemplateRendererInterface|MockObject */
    private $templates;
    /** @var PrismicTemplateHandler */
    private $subject;
    /** @var ServerRequestInterface */
    private $request;
    /** @var MockObject|Document */
    private $document;

    protected function setUp() : void
    {
        parent::setUp();
        $this->templates = $this->createMock(TemplateRendererInterface::class);
        $this->subject = new PrismicTemplateHandler($this->templates);
        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->document = $this->createMock(Document::class);
    }

    public function testThatTheTemplateMustBeKnownInAdvance() : void
    {
        $this->expectException(RequestError::class);
        $this->expectExceptionMessage('The request for "/foo" failed because there was no template attribute found in the request');
        $this->subject->handle($this->request);
    }

    private function requestHasTemplate() : ServerRequestInterface
    {
        return $this->request->withAttribute('template', 'template::foo');
    }

    public function testThatA404IsThrownWhenTheDocumentHasNotBeenResolved() : void
    {
        $request = $this->requestHasTemplate();
        $this->expectException(DocumentNotFound::class);
        $this->subject->handle($request);
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

        $response = $this->subject->handle($this->requestHasDocument());
        self::assertResponseIsSuccess($response);
        self::assertMessageBodyMatches($response, $this->equalTo('Some Markup'));
        self::assertMessageHasHeader($response, 'content-language', 'en-gb');
    }
}
