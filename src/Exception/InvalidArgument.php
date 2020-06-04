<?php
declare(strict_types=1);

namespace Primo\Exception;

use InvalidArgumentException;

class InvalidArgument extends InvalidArgumentException implements PrimoError
{
}
