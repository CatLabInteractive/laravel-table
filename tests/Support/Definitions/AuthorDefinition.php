<?php

namespace Tests\Support\Definitions;

use CatLab\Charon\Models\ResourceDefinition;
use Tests\Support\Models\Author;

class AuthorDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(Author::class);

        $this->identifier('id')->int();
        $this->field('name')->string()->visible(true, true);
    }
}
