<?php

declare(strict_types=1);

namespace Primo\Middleware;

use Laminas\Diactoros\Response\JsonResponse;
use Primo\Event\WebhookEvent;
use Prismic\Exception\JsonError;
use Prismic\Json;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class WebhookHandler implements RequestHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private string|null $expectedSecret = null,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = (string) $request->getBody();

        if (empty($body)) {
            return $this->jsonError('Bad Request', 400);
        }

        try {
            $payload = Json::decodeObject($body);
        } catch (JsonError) {
            return $this->jsonError('Invalid payload', 400);
        }

        if ($this->expectedSecret !== null && ($payload->secret ?? null) !== $this->expectedSecret) {
            return $this->jsonError('Invalid payload', 400);
        }

        $this->eventDispatcher->dispatch(WebhookEvent::new($payload));

        return new JsonResponse(['message' => 'Received'], 200);
    }

    private function jsonError(string $errorMessage, int $statusCode): ResponseInterface
    {
        return new JsonResponse(['error' => $errorMessage], $statusCode);
    }
}
