<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Http;

use PHPUnit\Framework\MockObject\MockObject;
use Primo\Http\PrismicHttpClientFactory;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;

class PrismicHttpClientFactoryTest extends TestCase
{
    /** @var MockObject|ClientInterface */
    private $container;

    protected function setUp() : void
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    private function clientInContainer(bool $value) : void
    {
        $this->container->expects($this->once())
            ->method('has')
            ->with(ClientInterface::class)
            ->willReturn($value);
    }

    public function testThatClientInContainerWillBeReturnedWhenAvailable() : void
    {
        $client = $this->createMock(ClientInterface::class);
        $this->clientInContainer(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with(ClientInterface::class)
            ->willReturn($client);

        $factory = new PrismicHttpClientFactory();
        $this->assertSame($client, $factory($this->container));
    }

    public function testThatClientDiscoveryWillBeUsedWhenNoClientIsInTheContainer() : void
    {
        $this->clientInContainer(false);
        $this->container->expects($this->never())->method('get');
        $factory = new PrismicHttpClientFactory();
        $factory($this->container);
        $this->addToAssertionCount(1);
    }
}
