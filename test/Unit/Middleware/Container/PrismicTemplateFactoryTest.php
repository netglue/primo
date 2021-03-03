<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Middleware\Container;

use Mezzio\Template\TemplateRendererInterface;
use Primo\Middleware\Container\PrismicTemplateFactory;
use PrimoTest\Unit\TestCase;
use Psr\Container\ContainerInterface;

class PrismicTemplateFactoryTest extends TestCase
{
    public function testFactory() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->willReturn(false);

        $container->expects(self::once())
            ->method('get')
            ->with(TemplateRendererInterface::class)
            ->willReturn($this->createMock(TemplateRendererInterface::class));

        $factory = new PrismicTemplateFactory();
        $factory->__invoke($container);
    }
}
