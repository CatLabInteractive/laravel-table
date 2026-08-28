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

    private function books(): \CatLab\Charon\Collections\ResourceCollection
    {
        return $this->toCollection([ new Book(1, 'Emma') ]);
    }

    public function testSortableColumnHeaderLinksToTheSortedUrlKeepingOtherFilters()
    {
        $table = (new Table($this->books(), new BookDefinition(), $this->indexContext(), '/books?status=draft&page=2'))
            ->sortable();

        $html = (string) $table->render();

        // page is dropped (sorting restarts at page 1), status is kept.
        $this->assertMatchesRegularExpression('#<a href="/books\?status=draft&amp;sort=title">\s*title#', $html);
        // status is filterable but not sortable: no sort link for it.
        $this->assertStringNotContainsString('sort=status', $html);
    }

    public function testSortableColumnHeaderTogglesDirectionAndShowsIt()
    {
        $ascending = (string) (new Table($this->books(), new BookDefinition(), $this->indexContext(), '/books?sort=title'))
            ->sortable()
            ->render();

        $this->assertStringContainsString('sort=%21title', $ascending);
        $this->assertMatchesRegularExpression('~title\s*&#9650;~', $ascending);

        $descending = (string) (new Table($this->books(), new BookDefinition(), $this->indexContext(), '/books?sort=!title'))
            ->sortable()
            ->render();

        $this->assertMatchesRegularExpression('#<a href="/books\?sort=title">#', $descending);
        $this->assertMatchesRegularExpression('~title\s*&#9660;~', $descending);
    }

    public function testHeadersStayPlainUnlessSortingIsEnabled()
    {
        $html = (string) (new Table($this->books(), new BookDefinition(), $this->indexContext(), '/books'))
            ->render();

        $this->assertStringNotContainsString('sort=', $html);
    }

    public function testFilterFormOffersEveryFilterableFieldPrefilledFromTheUrl()
    {
        $table = (new Table($this->books(), new BookDefinition(), $this->indexContext(), '/books?sort=title&title=Em&page=3'))
            ->filterable();

        $html = (string) $table->render();

        $this->assertStringContainsString('<form method="get" action="/books"', $html);
        $this->assertMatchesRegularExpression('#<input[^>]*name="title"[^>]*value="Em"#', $html);
        // enum fields become a select with every allowed value
        $this->assertMatchesRegularExpression('#<select[^>]*name="status"#', $html);
        $this->assertStringContainsString('<option value="draft"', $html);
        $this->assertStringContainsString('<option value="published"', $html);
        // sorting survives filtering, pagination does not
        $this->assertMatchesRegularExpression('#<input type="hidden" name="sort" value="title"#', $html);
        $this->assertStringNotContainsString('name="page"', $html);
    }

    public function testNoFilterFormUnlessFilteringIsEnabled()
    {
        $html = (string) (new Table($this->books(), new BookDefinition(), $this->indexContext(), '/books'))
            ->render();

        $this->assertStringNotContainsString('<form', $html);
    }
}
