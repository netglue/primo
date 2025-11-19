<?php

declare(strict_types=1);

namespace PrimoTest\Unit;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    use Psr7Assertions;
}
