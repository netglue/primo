<?php

declare(strict_types=1);

namespace Primo\Exception;

use RuntimeException;

class RuntimeError extends RuntimeException implements PrimoError
{
}
