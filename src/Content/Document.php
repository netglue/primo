<?php

declare(strict_types=1);

namespace Primo\Content;

use Prismic\Document as PrismicDocument;
use Prismic\Document\DocumentDataConsumer;
use Prismic\Document\Fragment;
use Prismic\Value\DocumentData;

class Document implements PrismicDocument
{
    use DocumentDataConsumer;

    public function __construct(DocumentData $data)
    {
        $this->data = $data;
    }

    public function get(string|int $name): Fragment
    {
        return $this->data->content()->get($name);
    }

    public function has(int|string $name): bool
    {
        return $this->data->content()->has($name);
    }
}
