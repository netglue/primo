<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Mezzio\Template\TemplateRendererInterface;
use Primo\Middleware\Container\PrismicTemplateHandlerFactory;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class PrismicTemplateHandlerFactoryTest extends TestCase
{
    public function testFactory() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $container->expects($this->once())
            ->method('get')
            ->with(TemplateRendererInterface::class)
            ->willReturn($this->createMock(TemplateRendererInterface::class));

        $factory = new PrismicTemplateHandlerFactory();
        $factory->__invoke($container);
    }
}
