<?php

namespace Tests\Feature;

use CatLab\Laravel\Table\Table;
use Tests\Support\Definitions\BookDefinition;
use Tests\Support\Models\Author;
use Tests\Support\Models\Book;
use Tests\Support\TestCase;

class TableTest extends TestCase
{
    public function testSingleRelationshipCellShowsTheChildsName()
    {
        $collection = $this->toCollection([
            new Book(1, 'Emma', new Author(7, 'Jane Austen')),
        ]);

        $html = (string) (new Table($collection, new BookDefinition(), $this->indexContext()))->render();

        $this->assertStringContainsString('Jane Austen', $html);
        $this->assertStringNotContainsString('relationship?', $html);
    }
}
