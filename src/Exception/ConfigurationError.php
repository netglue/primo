<?php
declare(strict_types=1);

namespace Primo\Exception;

use RuntimeException;

final class ConfigurationError extends RuntimeException implements PrimoError
{
}
