<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Router;

use Primo\Exception\InvalidArgument;
use Primo\Router\RouteParams;
use PrimoTest\Unit\TestCase;
use TypeError;

/** @psalm-suppress DeprecatedMethod */
class RouteParamsTest extends TestCase
{
    public function testOptionsArray(): RouteParams
    {
        $options = [
            'id'                => 'id',
            'uid'               => 'uid',
            'type'              => 'type',
            'bookmark'          => 'bookmark',
            'lang'              => 'lang',
            'reuseResultParams' => 'reuse',
        ];
        $params = RouteParams::fromArray($options);
        self::assertTrue(true);

        return $params;
    }

    /** @depends testOptionsArray */
    public function testId(RouteParams $params): void
    {
        self::assertSame('id', $params->id());
    }

    /** @depends testOptionsArray */
    public function testUid(RouteParams $params): void
    {
        self::assertSame('uid', $params->uid());
    }

    /** @depends testOptionsArray */
    public function testType(RouteParams $params): void
    {
        self::assertSame('type', $params->type());
    }

    /** @depends testOptionsArray */
    public function testBookmark(RouteParams $params): void
    {
        self::assertSame('bookmark', $params->bookmark());
    }

    /** @depends testOptionsArray */
    public function testLang(RouteParams $params): void
    {
        self::assertSame('lang', $params->lang());
    }

    /** @depends testOptionsArray */
    public function testReuseParams(RouteParams $params): void
    {
        self::assertSame('reuse', $params->reuseResultParams());
    }

    /** @return array<string, array{0: array<array-key, mixed>}> */
    public function typeErrorProvider(): array
    {
        return [
            'Non string option key' => [[0 => 'value']],
            'Non string option value' => [['id' => 1]],
        ];
    }

    /**
     * @param array<array-key, mixed> $options
     *
     * @dataProvider typeErrorProvider
     */
    public function testOptionTypeError(array $options): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress MixedArgumentTypeCoercion */
        RouteParams::fromArray($options);
    }

    public function testExceptionThrownForInvalidOptionKeys(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('"nuts" is not a valid option key. Valid options are:');
        RouteParams::fromArray(['nuts' => 'tasty']);
    }
}
