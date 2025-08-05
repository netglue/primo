<?php

declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\Route;

/** @final */
class ScoredRoute
{
    public function __construct(
        private readonly Route $route,
        private readonly int $score,
    ) {
    }

    public function route(): Route
    {
        return $this->route;
    }

    public function compare(self $other): int
    {
        return $this->score <=> $other->score;
    }
}
