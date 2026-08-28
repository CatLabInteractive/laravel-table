<?php

namespace Tests\Support\Models;

class Tag
{
    public function __construct(
        public int $id,
        public string $slug
    ) {
    }
}
