# laravel-table

Renders a [charon](https://github.com/CatLabInteractive/charon) `ResourceCollection` as a Bootstrap table.
Used by [laravel-charon-frontend](https://github.com/CatLabInteractive/laravel-charon-frontend) to build admin panels.

```php
use CatLab\Laravel\Table\Table;

$table = (new Table($collection, $resourceDefinition, $context, $request->getRequestUri()))
    ->sortable()      // headers of ->sortable() fields link to ?sort=field / ?sort=!field
    ->filterable()    // a GET form with one input per ->filterable() field (select for enums)
    ->setResourceUrlResolver(function (RESTResource $related) {
        return '/admin/things/' . $related->getIdentifiers()->getValues()[0]->getValue(); // or null
    })
    ->modelAction(...)
    ->collectionAction(...);

echo $table->render();
```

## Cells

Every visible property of a resource becomes a column; columns are the union over all rows, in first-seen order.

- **Scalar fields** render as-is.
- **Relationships** (expanded `ChildValue` / `ChildrenValue`) render as a comma separated list of related
  resources. Each is labelled by its `name`, `title` or `label` field when it has one, otherwise by `#<identifier>`;
  override with `setResourceLabelResolver()`. When a url resolver is set and returns a url, the label becomes a link.
- **Object / array fields** render as JSON.

## Sorting and filtering

Both are opt-in (`sortable()`, `filterable()`) and need the current url (4th constructor argument). They only
build urls and forms; the actual sorting and filtering is done by charon when the resulting request reaches the
API (`?sort=name`, `?sort=!name`, `?name=value`). Pagination parameters (`page`, `before`, `after`) are dropped
from those urls; every other query parameter is preserved.

## Views

Publish `resources/views` to `resources/views/vendor/table` to customise `table`, `cell`, `filters` and
`pagination`.
