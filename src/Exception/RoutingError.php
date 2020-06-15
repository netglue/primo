<?php
declare(strict_types=1);

namespace Primo\Exception;

use Mezzio\Router\RouteResult;
use Prismic\ResultSet;

use function count;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class RoutingError extends RuntimeError
{
    public static function uidMatchedWithoutType(RouteResult $routeResult) : self
    {
        return new static(sprintf(
            'The route named "%s" matches a Prismic UID, but the type cannot be resolved. You cannot resolve documents '
            . 'by UID when the type is not known. Matched parameters: %s',
            self::routeName($routeResult),
            self::matchedParams($routeResult)
        ), 400);
    }

    public static function nonUniqueResult(RouteResult $routeResult, ResultSet $resultSet) : self
    {
        return new static(sprintf(
            'The route named "%s" matched %d documents when transformed into a query. Route parameters were: %s',
            self::routeName($routeResult),
            count($resultSet),
            self::matchedParams($routeResult)
        ), 400);
    }

    protected static function routeName(RouteResult $routeResult) : string
    {
        $routeName = $routeResult->getMatchedRouteName();

        return $routeName ?: '[Unnamed Route]';
    }

    protected static function matchedParams(RouteResult $routeResult) : string
    {
        return json_encode($routeResult->getMatchedParams(), JSON_THROW_ON_ERROR);
    }
}
