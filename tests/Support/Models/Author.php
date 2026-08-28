<?php

namespace Tests\Support\Models;

class Author
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
