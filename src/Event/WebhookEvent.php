<?php

declare(strict_types=1);

namespace Primo\Event;

use DateTimeImmutable;
use DateTimeZone;
use Primo\Exception\RuntimeError;
use Prismic\Json;
use Serializable;

use function time;

final class WebhookEvent implements Serializable
{
    private int $received;

    private function __construct(private object $payload)
    {
        $this->received = time();
    }

    public static function new(object $payload): self
    {
        return new self($payload);
    }

    public function received(): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('U', (string) $this->received, new DateTimeZone('UTC'));
        if ($date === false) {
            throw new RuntimeError('Webhook event time evaluated to false');
        }

        return $date;
    }

    public function payload(): object
    {
        return $this->payload;
    }

    /** @deprecated */
    public function serialize(): string
    {
        return Json::encode([
            'received' => $this->received,
            'payload' => $this->payload,
        ]);
    }

    /**
     * @deprecated
     *
     * @param string $serialized
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function unserialize($serialized): void
    {
        $object = Json::decodeObject($serialized);
        $this->payload = $object->payload;
        $this->received = $object->received;
    }

    /** @return array{received: int, payload: string} */
    public function __serialize(): array
    {
        return [
            'received' => $this->received,
            'payload' => Json::encode($this->payload),
        ];
    }

    /** @param array{received: int, payload: string} $data */
    public function __unserialize(array $data): void
    {
        $this->received = $data['received'];
        $this->payload = Json::decodeObject($data['payload']);
    }
}
