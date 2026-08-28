<?php

namespace Tests\Support\Models;

class Book
{
    /**
     * @param Tag[] $tags
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?Author $author = null,
        public array $tags = [],
        public string $status = 'draft',
        public ?array $meta = null
    ) {
    }
}
