<?php

declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\Route;

class ScoredRoute
{
    public function __construct(
        private Route $route,
        private int $score,
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
