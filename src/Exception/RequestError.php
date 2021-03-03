<?php

declare(strict_types=1);

namespace Primo\Exception;

use Psr\Http\Message\RequestInterface;
use RuntimeException;

use function sprintf;

class RequestError extends RuntimeException implements PrimoError
{
    /** @var RequestInterface|null */
    private $request;

    public static function withRequest(RequestInterface $request, string $message, int $code): self
    {
        $error = new static($message, $code);
        $error->request = $request;

        return $error;
    }

    public static function withMissingRouteResult(RequestInterface $request): self
    {
        $message = sprintf(
            'The request for %s failed because the route result was not available. This means that routing has ' .
            'either not yet occurred or a route could not be matched.',
            (string) $request->getUri()
        );

        return self::withRequest($request, $message, 500);
    }

    public static function withMissingTemplateAttribute(RequestInterface $request, string $expectedTemplateAttribute): self
    {
        $message = sprintf(
            'The request for "%s" failed because there was no template attribute found in the request. I was ' .
            'expecting to find a template attribute named "%s"',
            (string) $request->getUri(),
            $expectedTemplateAttribute
        );

        return self::withRequest($request, $message, 500);
    }

    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }
}
