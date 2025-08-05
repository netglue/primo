<?php

declare(strict_types=1);

namespace PrimoTest\Unit\Asset;

use Prismic\Document;
use Prismic\Document\DocumentDataConsumer;

final class BadHierarchy implements Document
{
    use DocumentDataConsumer;
}
