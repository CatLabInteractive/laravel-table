<?php

namespace CatLab\Laravel\Table;

use CatLab\Charon\Collections\ResourceCollection;
use CatLab\Charon\Enums\FilterOperator;
use CatLab\Charon\Interfaces\Context;
use CatLab\Charon\Interfaces\ResourceDefinition;
use CatLab\Charon\Interfaces\ResourceTransformer;
use CatLab\Charon\Models\Properties\ResourceField;
use CatLab\Charon\Models\RESTResource;
use CatLab\Charon\Models\Values\Base\RelationshipValue;
use CatLab\Charon\Models\Values\Base\Value;
use CatLab\Laravel\Table\Models\Action;
use CatLab\Laravel\Table\Models\Cell;
use CatLab\Laravel\Table\Models\CollectionAction;
use CatLab\Laravel\Table\Models\Column;
use CatLab\Laravel\Table\Models\Filter;
use CatLab\Laravel\Table\Models\RelatedResource;
use CatLab\Laravel\Table\Support\QueryUrl;
use CatLab\Laravel\Table\Models\ModelAction;
use CatLab\Laravel\Table\Models\Pagination;
use CatLab\Requirements\Enums\PropertyType;
use Illuminate\Support\HtmlString;

/**
 * Class Table
 * @package CatLab\CharonFrontend
 */
class Table
{
    /**
     * @var ResourceCollection
     */
    private $resourceCollection;

    /**
     * @var ResourceDefinition
     */
    private $definition;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Action[]
     */
    private $modelActions;

    /**
     * @var Action[]
     */
    private $collectionActions;

    /**
     * @var string
     */
    private $currentUrl;

    /**
     * Query parameters that never carry over into sort / filter urls.
     * @var string[]
     */
    protected $paginationQueryParameters = [
        'page',
        'before',
        'after'
    ];

    /**
     * @var bool
     */
    private $sortable = false;

    /**
     * @var bool
     */
    private $filterable = false;

    /**
     * Display names of the fields that get no column.
     * @var string[]
     */
    private $hiddenColumns = [];

    /**
     * Display names of the fields that get no filter.
     * @var string[]
     */
    private $hiddenFilters = [];

    /**
     * @var \Closure|null
     */
    private $resourceUrlResolver;

    /**
     * @var \Closure|null
     */
    private $resourceLabelResolver;

    /**
     * Table constructor.
     * @param ResourceCollection $collection
     * @param ResourceDefinition $definition
     * @param Context $context
     * @param string $currentUrl
     */
    public function __construct(
        ResourceCollection $collection,
        ResourceDefinition $definition,
        Context $context,
        $currentUrl = null
    ) {
        $this->resourceCollection = $collection;
        $this->definition = $definition;
        $this->context = $context;
        $this->modelActions = [];
        $this->collectionActions = [];
        $this->currentUrl = $currentUrl;
    }

    /**
     * @param ModelAction $action
     * @return $this
     */
    public function modelAction(ModelAction $action)
    {
        $this->modelActions[] = $action;
        return $this;
    }

    /**
     * @param CollectionAction $action
     * @return $this
     */
    public function collectionAction(CollectionAction $action)
    {
        $this->collectionActions[] = $action;
        return $this;
    }

    /**
     * Turn the headers of sortable fields (ResourceField::sortable()) into
     * links that sort the collection through charon's "sort" parameter.
     * Requires a current url.
     * @param bool $sortable
     * @return $this
     */
    public function sortable($sortable = true)
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * Show a filter form for the filterable fields
     * (ResourceField::filterable()). Requires a current url.
     * @param bool $filterable
     * @return $this
     */
    public function filterable($filterable = true)
    {
        $this->filterable = $filterable;
        return $this;
    }

    /**
     * Leave these fields (by display name) out of the table, e.g. an
     * identifier that means nothing to the reader. Their values stay in the
     * resources, so actions and resolvers still see them.
     * @param string[] $names
     * @return $this
     */
    public function hideColumns(array $names)
    {
        $this->hiddenColumns = array_values(array_unique(array_merge($this->hiddenColumns, $names)));
        return $this;
    }

    /**
     * Leave these filterable fields (by display name) out of the filter form.
     * @param string[] $names
     * @return $this
     */
    public function hideFilters(array $names)
    {
        $this->hiddenFilters = array_values(array_unique(array_merge($this->hiddenFilters, $names)));
        return $this;
    }

    /**
     * Resolve the url a related resource (as shown in a relationship cell)
     * links to. Null suppresses the link -- unlike the label resolver's null,
     * which delegates to a default, this one is the final answer.
     * @param \Closure $resolver function(RESTResource $resource): ?string
     * @return $this
     */
    public function setResourceUrlResolver(\Closure $resolver)
    {
        $this->resourceUrlResolver = $resolver;
        return $this;
    }

    /**
     * Resolve the label a related resource is shown as, overriding the
     * default "name-like field, else identifier" heuristic. A resolver that
     * returns null has no opinion about this particular resource and leaves
     * it to that heuristic, so a caller can name the few resource types it
     * knows about without having to reimplement the fallback for the rest.
     * @param \Closure $resolver function(RESTResource $resource): ?string
     * @return $this
     */
    public function setResourceLabelResolver(\Closure $resolver)
    {
        $this->resourceLabelResolver = $resolver;
        return $this;
    }

    /**
     * @return HtmlString
     */
    public function render()
    {
        // Columns are the union of every resource's visible properties, in
        // first-seen order: a polymorphic collection (e.g. workflow steps of
        // different types) does not share one key set, so the first item
        // alone would leave later rows with too few or misaligned cells.
        $columns = [];
        $labels = [];
        $rows = [];
        foreach ($this->resourceCollection as $resource) {
            /** @var RESTResource $resource */
            $cells = [];
            foreach ($resource->getProperties()->getValues() as $value) {
                /** @var Value $value */
                if (!$value->isVisible()) {
                    continue;
                }

                $key = $value->getField()->getDisplayName();
                if (in_array($key, $this->hiddenColumns, true)) {
                    continue;
                }

                if (!in_array($key, $columns, true)) {
                    $columns[] = $key;
                    $labels[$key] = $this->getFieldLabel($value->getField());
                }

                $cells[$key] = $this->makeCell($value);
            }

            // Buttons (an icon, or a label for an action that asks for it)
            // sit next to the row, the rest go in the row's dropdown menu.
            $iconActions = [];
            $menuActions = [];
            foreach ($this->modelActions as $action) {
                if (!$action->shouldShow($resource)) {
                    continue;
                }

                if ($action->isButton()) {
                    $iconActions[] = $action;
                } else {
                    $menuActions[] = $action;
                }
            }

            $rows[] = [
                'resource' => $resource,
                'cells' => $cells,
                'iconActions' => $iconActions,
                'menuActions' => $menuActions
            ];
        }

        $pagination = null;
        $currentUrl = null;
        if ($this->currentUrl) {
            $pagination = new Pagination($this->resourceCollection, $this->currentUrl);
            $currentUrl = new QueryUrl($this->currentUrl);
        }

        $filters = $this->makeFilters($currentUrl);

        return new HtmlString(view('table::table', [
            'columns' => $this->makeColumns($columns, $currentUrl, $labels),
            'filters' => $filters,
            'filterAction' => $currentUrl ? $currentUrl->getPath() : null,
            'filterClearUrl' => $currentUrl ? $this->getFilterClearUrl($currentUrl, $filters) : null,
            'filterHiddenParameters' => $currentUrl ? $this->getFilterHiddenParameters($currentUrl) : [],
            'rows' => $rows,
            'modelActions' => $this->modelActions,
            'collectionActions' => $this->collectionActions,
            'pagination' => $pagination
        ])->__toString());
    }

    /**
     * @param string[] $keys
     * @param QueryUrl|null $currentUrl
     * @param string[] $labels key => header
     * @return Column[]
     */
    protected function makeColumns(array $keys, ?QueryUrl $currentUrl, array $labels = [])
    {
        $sortableFields = [];
        if ($this->sortable && $currentUrl) {
            foreach ($this->getResourceFields() as $field) {
                if ($field->isSortable()) {
                    $sortableFields[$field->getDisplayName()] = $field;
                }
            }
        }

        $currentSort = $currentUrl ? (string) $currentUrl->get(ResourceTransformer::SORT_PARAMETER) : '';

        $columns = [];
        foreach ($keys as $key) {
            if (!isset($sortableFields[$key])) {
                $columns[] = new Column($key, null, null, $labels[$key] ?? null);
                continue;
            }

            $direction = null;
            $nextSort = $key;
            if ($currentSort === $key) {
                $direction = Column::ASC;
                $nextSort = '!' . $key;
            } elseif ($currentSort === '!' . $key) {
                $direction = Column::DESC;
            }

            $columns[] = new Column(
                $key,
                $currentUrl->with(
                    [ ResourceTransformer::SORT_PARAMETER => $nextSort ],
                    $this->paginationQueryParameters
                ),
                $direction,
                $labels[$key] ?? null
            );
        }

        return $columns;
    }

    /**
     * @param QueryUrl|null $currentUrl
     * @return Filter[]
     */
    protected function makeFilters(?QueryUrl $currentUrl)
    {
        if (!$this->filterable || !$currentUrl) {
            return [];
        }

        $filters = [];
        foreach ($this->getResourceFields() as $field) {
            if (!$field->isFilterable()) {
                continue;
            }

            $name = $field->getDisplayName();
            if (in_array($name, $this->hiddenFilters, true)) {
                continue;
            }

            $value = $currentUrl->get($name);
            $operator = null;
            if (is_array($value)) {
                $operator = isset($value['op']) && is_string($value['op']) ? $value['op'] : null;
                $value = $value['value'] ?? null;
            }

            $options = $field->getAllowedValues() ?: null;
            $operators = $this->getFilterOperators($field, $options !== null);
            if ($operator !== null && $operator !== 'eq' && !in_array($operator, $operators, true)) {
                $operator = null;
            }

            $filters[] = new Filter(
                $name,
                is_scalar($value) ? (string) $value : null,
                $options,
                $this->getFieldLabel($field),
                $operator,
                $operators
            );
        }

        return $filters;
    }

    /**
     * The operators (request names of CatLab\Charon\Enums\FilterOperator) a
     * filter offers: is, is not, contains (text without fixed options), is
     * empty and is not empty.
     * @param ResourceField $field
     * @param bool $hasOptions
     * @return string[]
     */
    protected function getFilterOperators($field, $hasOptions)
    {
        $operators = [ FilterOperator::REQUEST_EQ, FilterOperator::REQUEST_NEQ ];

        if (!$hasOptions && in_array($field->getType(), [ PropertyType::STRING, PropertyType::HTML ], true)) {
            $operators[] = FilterOperator::REQUEST_CONTAINS;
        }

        $operators[] = FilterOperator::REQUEST_EMPTY;
        $operators[] = FilterOperator::REQUEST_NOT_EMPTY;

        return $operators;
    }

    /**
     * The current url without any filter or pagination parameter: what "clear" links to.
     * @param QueryUrl $currentUrl
     * @param Filter[] $filters
     * @return string
     */
    protected function getFilterClearUrl(QueryUrl $currentUrl, array $filters)
    {
        $unset = $this->paginationQueryParameters;
        foreach ($filters as $filter) {
            $unset[] = $filter->getName();
        }

        return $currentUrl->with([], $unset);
    }

    /**
     * Query parameters the filter form must carry along as hidden inputs:
     * everything that is neither a filter nor pagination.
     * @param QueryUrl $currentUrl
     * @return array
     */
    protected function getFilterHiddenParameters(QueryUrl $currentUrl)
    {
        $filterNames = [];
        foreach ($this->makeFilters($currentUrl) as $filter) {
            $filterNames[] = $filter->getName();
        }

        $hidden = [];
        foreach ($currentUrl->getQuery() as $key => $value) {
            if (
                in_array($key, $filterNames, true) ||
                in_array($key, $this->paginationQueryParameters, true) ||
                !is_scalar($value)
            ) {
                continue;
            }
            $hidden[$key] = $value;
        }

        return $hidden;
    }

    /**
     * What a field is called in a header or on a filter: the label() of its
     * definition, and without one its display name in words ("updated_at"
     * and "updatedAt" both become "Updated at").
     * @param \CatLab\Charon\Models\Properties\Base\Field $field
     * @return string
     */
    protected function getFieldLabel($field)
    {
        $name = $field->getDisplayName();

        $label = $field->getLabel();
        if (is_string($label) && $label !== '' && $label !== ucfirst($name)) {
            return $label;
        }

        $words = preg_replace('/(?<=[a-z0-9])([A-Z])/', ' $1', $name);
        $words = str_replace([ '_', '-' ], ' ', $words);

        return ucfirst(strtolower(trim($words)));
    }

    /**
     * @return ResourceField[]
     */
    protected function getResourceFields()
    {
        $out = [];
        foreach ($this->definition->getFields() as $field) {
            if ($field instanceof ResourceField) {
                $out[] = $field;
            }
        }
        return $out;
    }

    /**
     * Build the cell for one property value. Public so a single value
     * (e.g. a relationship on a detail page) can be rendered through the
     * same rules and resolvers as the table: view('table::cell', ['cell' => $cell]).
     * @param Value $value
     * @return Cell
     */
    public function makeCell(Value $value)
    {
        if ($value instanceof RelationshipValue) {
            $related = [];
            foreach ($value->getChildren() as $child) {
                if ($child instanceof RESTResource) {
                    $related[] = new RelatedResource(
                        $this->getResourceLabel($child),
                        $this->getResourceUrl($child)
                    );
                }
            }
            return Cell::relationship($related);
        }

        $raw = $value->toArray();
        if (is_array($raw)) {
            return Cell::object($raw);
        }

        return Cell::scalar($raw);
    }

    /**
     * Human readable label for a related resource: whatever the resolver set
     * through setResourceLabelResolver() says, and otherwise -- or when that
     * resolver returns null -- a name-like field when the resource has one,
     * its identifier when it doesn't. A name-like field that is the empty
     * string doesn't count: it would render as a label nobody can see, and
     * as an invisible link when a url resolver is set.
     * @param RESTResource $resource
     * @return string
     */
    protected function getResourceLabel(RESTResource $resource)
    {
        if ($this->resourceLabelResolver) {
            $label = call_user_func($this->resourceLabelResolver, $resource);
            if ($label !== null) {
                return (string) $label;
            }
        }

        $values = $resource->toArray();
        foreach ([ 'name', 'title', 'label' ] as $candidate) {
            if (isset($values[$candidate]) && is_scalar($values[$candidate]) && $values[$candidate] !== '') {
                return (string) $values[$candidate];
            }
        }

        $identifiers = $resource->getIdentifiers()->getValues();
        if (count($identifiers) > 0) {
            return '#' . $identifiers[0]->getValue();
        }

        return '?';
    }

    /**
     * @param RESTResource $resource
     * @return string|null
     */
    protected function getResourceUrl(RESTResource $resource)
    {
        if (!$this->resourceUrlResolver) {
            return null;
        }

        return call_user_func($this->resourceUrlResolver, $resource);
    }
}
