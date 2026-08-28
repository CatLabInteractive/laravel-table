<?php

namespace Tests\Feature;

use CatLab\Charon\Models\RESTResource;
use CatLab\Laravel\Table\Table;
use Tests\Support\Definitions\BookDefinition;
use Tests\Support\Models\Author;
use Tests\Support\Models\Book;
use Tests\Support\Models\Tag;
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

    public function testManyRelationshipCellListsEveryChild()
    {
        $collection = $this->toCollection([
            new Book(1, 'Emma', null, [ new Tag(1, 'fiction'), new Tag(2, 'classic') ]),
        ]);

        $html = (string) (new Table($collection, new BookDefinition(), $this->indexContext()))->render();

        // Tag has no name-like field, so both fall back to their identifier.
        $this->assertMatchesRegularExpression('/#1\s*,\s*#2/', $html);
    }

    public function testObjectFieldRendersItsContentInsteadOfThePlaceholder()
    {
        $collection = $this->toCollection([
            new Book(1, 'Emma', null, [], 'draft', [ 'pages' => 474 ]),
        ]);

        $html = (string) (new Table($collection, new BookDefinition(), $this->indexContext()))->render();

        $this->assertStringContainsString('pages', $html);
        $this->assertStringContainsString('474', $html);
        $this->assertStringNotContainsString('relationship?', $html);
    }

    public function testRelatedResourcesLinkToTheUrlGivenByTheResolver()
    {
        $collection = $this->toCollection([
            new Book(1, 'Emma', new Author(7, 'Jane Austen')),
        ]);

        $table = (new Table($collection, new BookDefinition(), $this->indexContext()))
            ->setResourceUrlResolver(function (RESTResource $resource) {
                return '/admin/authors/' . $resource->getIdentifiers()->getValues()[0]->getValue();
            });

        $html = (string) $table->render();

        $this->assertMatchesRegularExpression('#<a href="/admin/authors/7">\s*Jane Austen\s*</a>#', $html);
    }

    public function testRelatedResourcesStayPlainTextWhenTheResolverReturnsNull()
    {
        $collection = $this->toCollection([
            new Book(1, 'Emma', new Author(7, 'Jane Austen')),
        ]);

        $table = (new Table($collection, new BookDefinition(), $this->indexContext()))
            ->setResourceUrlResolver(function () {
                return null;
            });

        $html = (string) $table->render();

        $this->assertStringContainsString('Jane Austen', $html);
        $this->assertStringNotContainsString('<a href', $html);
    }

    public function testRelatedResourceLabelCanBeOverridden()
    {
        $collection = $this->toCollection([
            new Book(1, 'Emma', new Author(7, 'Jane Austen')),
        ]);

        $table = (new Table($collection, new BookDefinition(), $this->indexContext()))
            ->setResourceLabelResolver(function (RESTResource $resource) {
                return strtoupper($resource->toArray()['name']);
            });

        $html = (string) $table->render();

        $this->assertStringContainsString('JANE AUSTEN', $html);
    }
}
