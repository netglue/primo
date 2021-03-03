<?php

declare(strict_types=1);

namespace Primo\Router;

use Primo\Exception\InvalidArgument;

use function implode;
use function in_array;
use function sprintf;

final class RouteParams
{
    /** @var string */
    private $id = 'document-id';
    /** @var string */
    private $uid = 'document-uid';
    /** @var string */
    private $type = 'document-type';
    /** @var string */
    private $bookmark = 'document-bookmark';
    /** @var string */
    private $lang = 'document-lang';
    /** @var string */
    private $tag = 'document-tag';
    /** @var string */
    private $reuseResultParams = 'reuse_result_params';
    /** @var string[] */
    private static $acceptable = ['key', 'id', 'uid', 'type', 'bookmark', 'lang', 'tag', 'reuseResultParams'];

    private function __construct()
    {
    }

    /** @param string[] $options */
    public static function fromArray(array $options): self
    {
        $params = new static();
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
                implode(', ', self::$acceptable)
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
