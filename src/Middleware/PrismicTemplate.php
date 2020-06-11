<?php
declare(strict_types=1);

namespace Primo\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Primo\Exception\RequestError;
use Prismic\Document;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PrismicTemplate implements MiddlewareInterface
{
    public const DEFAULT_TEMPLATE_ATTRIBUTE = 'template';

    /** @var TemplateRendererInterface */
    private $renderer;
    /** @var string */
    private $templateAttribute;

    public function __construct(TemplateRendererInterface $renderer, string $templateAttribute = self::DEFAULT_TEMPLATE_ATTRIBUTE)
    {
        $this->renderer = $renderer;
        $this->templateAttribute = $templateAttribute;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $template = $request->getAttribute($this->templateAttribute);
        if (! $template) {
            throw RequestError::withMissingTemplateAttribute($request, $this->templateAttribute);
        }

        $document = $request->getAttribute(Document::class);
        if (! $document instanceof Document) {
            return $handler->handle($request);
        }

        $this->renderer->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'document',
            $document
        );

        return new HtmlResponse($this->renderer->render($template), 200, [
            'Content-Language' => $document->lang(),
        ]);
    }
}
