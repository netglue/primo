<?php
declare(strict_types=1);

namespace Primo\Router;

use Mezzio\Router\Route;

class ScoredRoute
{
    /** @var Route */
    private $route;

    /** @var int */
    private $score;

    public function __construct(Route $route, int $score)
    {
        $this->route = $route;
        $this->score = $score;
    }

    public function route() : Route
    {
        return $this->route;
    }

    public function compare(self $other) : int
    {
        return $this->score <=> $other->score;
    }
}
