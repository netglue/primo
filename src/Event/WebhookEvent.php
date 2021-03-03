<?php

declare(strict_types=1);

namespace Primo\Event;

use DateTimeImmutable;
use DateTimeZone;
use Prismic\Json;
use Serializable;

use function time;

final class WebhookEvent implements Serializable
{
    /** @var int */
    private $received;
    /** @var object */
    private $payload;

    private function __construct(object $payload)
    {
        $this->payload = $payload;
        $this->received = time();
    }

    public static function new(object $payload): self
    {
        return new static($payload);
    }

    public function received(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat('U', (string) $this->received, new DateTimeZone('UTC'));
    }

    public function payload(): object
    {
        return $this->payload;
    }

    public function serialize(): string
    {
        return Json::encode([
            'received' => $this->received,
            'payload' => $this->payload,
        ]);
    }

    /** @param mixed $serialized */
    public function unserialize($serialized): void
    {
        $object = Json::decodeObject($serialized);
        $this->payload = $object->payload;
        $this->received = $object->received;
    }
}
