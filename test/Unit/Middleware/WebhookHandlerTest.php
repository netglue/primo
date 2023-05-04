<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Middleware;

use Http\Discovery\Psr17FactoryDiscovery;
use PHPUnit\Framework\MockObject\MockObject;
use Primo\Middleware\WebhookHandler;
use PrimoTest\Unit\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebhookHandlerTest extends TestCase
{
    private MockObject|EventDispatcherInterface $events;
    private ServerRequestInterface $request;
    private WebhookHandler $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->events = $this->createMock(EventDispatcherInterface::class);
        $this->request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', '/foo');
        $this->subject = new WebhookHandler($this->events, 'secret');
    }

    public function testAnEmptyRequestBodyIsABadRequest(): void
    {
        $response = $this->subject->handle($this->request);
        self::assertResponseHasStatus($response, 400);
    }

    public function testAnInvalidPayloadIsABadRequest(): void
    {
        $stream = Psr17FactoryDiscovery::findStreamFactory()->createStream('Not JSON');
        $request = $this->request->withBody($stream);
        $response = $this->subject->handle($request);
        self::assertResponseHasStatus($response, 400);
    }

    /** @return mixed[] */
    public function theWrongSecret(): iterable
    {
        return [
            'Missing Secret' => ['{"not-defined":"foo"}'],
            'Incorrect Secret' => ['{"secret":"wrong"}'],
        ];
    }

    /** @dataProvider theWrongSecret */
    public function testUnsuccessfulSecrets(string $body): void
    {
        $stream = Psr17FactoryDiscovery::findStreamFactory()->createStream($body);
        $request = $this->request->withBody($stream);
        $response = $this->subject->handle($request);
        self::assertResponseHasStatus($response, 400);
    }

    public function testPayloadWithTheCorrectSecretIsSuccessful(): void
    {
        $body = '{"secret":"secret"}';
        $stream = Psr17FactoryDiscovery::findStreamFactory()->createStream($body);
        $request = $this->request->withBody($stream);
        $response = $this->subject->handle($request);
        self::assertResponseIsSuccess($response);
    }

    /** @dataProvider theWrongSecret */
    public function testPayloadIsSuccessfulWhenSecretIsNotRequired(string $body): void
    {
        $subject = new WebhookHandler($this->events, null);
        $stream = Psr17FactoryDiscovery::findStreamFactory()->createStream($body);
        $request = $this->request->withBody($stream);
        $response = $subject->handle($request);
        self::assertResponseIsSuccess($response);
    }
}
