<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Event;

use Primo\Event\WebhookEvent;
use PrimoTest\Unit\TestCase;
use stdClass;

use function assert;
use function serialize;
use function unserialize;

class WebhookEventTest extends TestCase
{
    public function testThatEventsCanBeSerialised(): void
    {
        $payload = new stdClass();
        $payload->foo = 'baz';
        $event = WebhookEvent::new($payload);

        $data = serialize($event);

        $copy = unserialize($data);
        assert($copy instanceof WebhookEvent);

        self::assertEquals(
            $event->received(),
            $copy->received(),
        );
        self::assertSame(
            $event->payload()->foo,
            $copy->payload()->foo,
        );
    }
}
