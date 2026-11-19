# laravel-table

Renders a [charon](https://github.com/CatLabInteractive/charon) `ResourceCollection` as a Bootstrap table.
Used by [laravel-charon-frontend](https://github.com/CatLabInteractive/laravel-charon-frontend) to build admin panels.

```php
use CatLab\Laravel\Table\Table;

$table = (new Table($collection, $resourceDefinition, $context, $request->getRequestUri()))
    ->sortable()      // headers of ->sortable() fields link to ?sort=field / ?sort=!field
    ->filterable()    // a GET form with one input per ->filterable() field (select for enums)
    ->setResourceUrlResolver(function (RESTResource $related) {
        return '/admin/things/' . $related->getIdentifiers()->getValues()[0]->getValue(); // null suppresses the link
    })
    ->setResourceLabelResolver(function (RESTResource $related) {
        return $related->toArray()['uid'] ?? null; // null falls back to the default heuristic
    })
    ->modelAction(...)
    ->collectionAction(...);

echo $table->render();
```

## Cells

Every visible property of a resource becomes a column; columns are the union over all rows, in first-seen order.
The header is the field's `label()` from the resource definition, and without one its display name in words
(`created_at` and `createdAt` both become "Created at"). `hideColumns([ 'type' ])` leaves fields (by display
name) out of the table; their values stay in the resources, so actions and resolvers still see them.

- **Scalar fields** render as-is.
- **Relationships** (expanded `ChildValue` / `ChildrenValue`) render as a comma separated list of related
  resources. Each is labelled by its `name`, `title` or `label` field when it has a non-empty one, otherwise by `#<identifier>`;
  override with `setResourceLabelResolver()`, which may return `null` for a resource it has no name for and leave
  that one to the default. When a url resolver is set and returns a url, the label becomes a link.
- **Object / array fields** render as JSON.

## Sorting and filtering

Both are opt-in (`sortable()`, `filterable()`) and need the current url (4th constructor argument). They only
build urls and forms; the actual sorting and filtering is done by charon when the resulting request reaches the
API (`?sort=name`, `?sort=!name`, `?name=value`). Pagination parameters (`page`, `before`, `after`) are dropped
from those urls; every other query parameter is preserved.

The filter form is a Bootstrap 3 `form-inline` bar (`.table-filters`): one small input group per filterable field
labelled like the column headers, a search button and - when a filter is active - a "Clear"
link back to the unfiltered url. `hideFilters([ 'type' ])` leaves filterable fields out of the form.

Each filter has a condition in front of its value: is, is not, contains (free text only), is empty and is not
empty. The form sends `name[op]` and `name[value]`, which charon (^1.13, `FilterOperator`) turns into the query;
"is" is the blank default, so an untouched form leaves the url clean and `?name=value` still means "is". The
labels are translated (`table::table`, en and nl).

## Row actions

`modelAction()` adds a link to every row (hide it per row with `setCondition()`). An action with
`setIcon('edit')` is drawn as a small icon button with its label as tooltip; all other actions go in a dropdown
menu behind a "more" button, which uses Bootstrap's dropdown plugin (`data-toggle` and `data-bs-toggle` are
both set, so Bootstrap 4 and 5 work). The icons `view`, `edit` and `delete` ship with the package as inline SVG;
the `delete` button is drawn in the danger colour.

```php
$table->modelAction((new ModelAction('BookController@edit', 'Edit'))->setIcon('edit'));
$table->modelAction(new ModelAction('BookController@export', 'Export')); // in the dropdown
```

An action can also be drawn and done differently:

- `showLabel()`: a button with its text next to the row, instead of an icon or a menu entry.
- `setStyle('primary')`: the Bootstrap button style of an icon or labelled button.
- `setMethod('post')` (or `put`, `patch`, `delete`): the action is a small form with a csrf token instead of a
  link, also inside the menu.
- `setConfirm('Archive this book?')`: a browser confirm before the link is followed or the form is sent.
- `setOptions('shelf', fn ($book) => [ 3 => 'Classics' ], fn ($book) => 3)`: a select in front of the button,
  posted under that name (the method becomes `post`); the third argument preselects a value.

```php
$table->modelAction(
    (new ModelAction('BookController@archive', 'Archive'))
        ->showLabel()
        ->setStyle('outline-danger')
        ->setMethod('post')
        ->setConfirm('Archive this book?')
);
```

## Views

Publish `resources/views` to `resources/views/vendor/table` to customise `table`, `action`, `cell`, `filters` and
`pagination`, or to add icons (`icons/<name>.blade.php`).
