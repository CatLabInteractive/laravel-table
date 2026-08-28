<?php

namespace Tests\Support\Definitions;

use CatLab\Charon\Models\ResourceDefinition;
use Tests\Support\Models\Book;

class BookDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(Book::class);

        $this->identifier('id')->int();

        $this->field('title')
            ->string()
            ->sortable()
            ->filterable()
            ->visible(true, true);

        $this->field('status')
            ->string()
            ->enum(['draft', 'published'])
            ->filterable()
            ->visible(true, true);

        $this->field('meta')
            ->object()
            ->visible(true, true);

        $this->relationship('author', AuthorDefinition::class)
            ->one()
            ->expanded()
            ->visible(true, true);

        $this->relationship('tags', TagDefinition::class)
            ->many()
            ->expanded()
            ->visible(true, true);
    }
}
