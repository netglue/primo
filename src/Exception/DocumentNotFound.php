<?php
declare(strict_types=1);

namespace Primo\Exception;

use Psr\Http\Message\RequestInterface;

use function sprintf;

final class DocumentNotFound extends RequestError
{
    public static function with(RequestInterface $request) : self
    {
        $message = sprintf(
            'No document can be found for the url %s',
            (string) $request->getUri()
        );

        return self::withRequest($request, $message, 404);
    }
}
