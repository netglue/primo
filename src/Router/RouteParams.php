<?php

declare(strict_types=1);

namespace Primo\Router;

use Primo\Exception\InvalidArgument;

use function implode;
use function in_array;
use function sprintf;

/** @psalm-suppress DeprecatedProperty */
final class RouteParams
{
    private string $id = 'document-id';
    private string $uid = 'document-uid';
    private string $type = 'document-type';
    /** @deprecated */
    private string $bookmark = 'document-bookmark';
    private string $lang = 'document-lang';
    private string $tag = 'document-tag';
    private string $reuseResultParams = 'reuse_result_params';
    /** @var string[] */
    private static array $acceptable = ['key', 'id', 'uid', 'type', 'bookmark', 'lang', 'tag', 'reuseResultParams'];

    private function __construct()
    {
    }

    /** @param array<string, string> $options */
    public static function fromArray(array $options): self
    {
        $params = new self();
        foreach ($options as $name => $value) {
            $params->setParameter($name, $value);
        }

        return $params;
    }

    private function setParameter(string $name, string $value): void
    {
        if (! in_array($name, self::$acceptable, true)) {
            throw new InvalidArgument(sprintf(
                '"%s" is not a valid option key. Valid options are: %s',
                $name,
                implode(', ', self::$acceptable),
            ));
        }

        $this->{$name} = $value;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function uid(): string
    {
        return $this->uid;
    }

    public function type(): string
    {
        return $this->type;
    }

    /** @deprecated */
    public function bookmark(): string
    {
        return $this->bookmark;
    }

    public function lang(): string
    {
        return $this->lang;
    }

    public function reuseResultParams(): string
    {
        return $this->reuseResultParams;
    }

    public function tag(): string
    {
        return $this->tag;
    }
}
