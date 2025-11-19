<?php

declare(strict_types=1);

namespace PrimoTest\Unit;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

trait Psr7Assertions
{
    public static function assertMessageHasHeader(
        MessageInterface $message,
        string $headerName,
        Constraint|null $headerValue = null,
    ): void {
        Assert::assertTrue($message->hasHeader($headerName), 'The message does not contain the expected header');

        if ($headerValue === null) {
            return;
        }

        Assert::assertThat(
            $message->getHeaderLine($headerName),
            $headerValue,
        );
    }

    public static function assertMessageBodyMatches(MessageInterface $message, Constraint $expect): void
    {
        $body = $message->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        Assert::assertThat(
            $body->getContents(),
            $expect,
        );
    }

    public static function assertResponseHasStatus(ResponseInterface $response, int $status): void
    {
        Assert::assertSame($status, $response->getStatusCode());
    }

    public static function assertResponseIsSuccess(ResponseInterface $response): void
    {
        Assert::assertGreaterThanOrEqual(200, $response->getStatusCode());
        Assert::assertLessThan(300, $response->getStatusCode());
    }
}
