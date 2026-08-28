<?php

namespace CatLab\Laravel\Table;

use CatLab\Charon\Collections\ResourceCollection;
use CatLab\Charon\Interfaces\Context;
use CatLab\Charon\Interfaces\ResourceDefinition;
use CatLab\Charon\Models\RESTResource;
use CatLab\Charon\Models\Values\Base\RelationshipValue;
use CatLab\Charon\Models\Values\Base\Value;
use CatLab\Laravel\Table\Models\Action;
use CatLab\Laravel\Table\Models\Cell;
use CatLab\Laravel\Table\Models\CollectionAction;
use CatLab\Laravel\Table\Models\RelatedResource;
use CatLab\Laravel\Table\Models\ModelAction;
use CatLab\Laravel\Table\Models\Pagination;
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
     * Resolve the url a related resource (as shown in a relationship cell)
     * links to. Return null for "no link".
     * @param \Closure $resolver function(RESTResource $resource): ?string
     * @return $this
     */
    public function setResourceUrlResolver(\Closure $resolver)
    {
        $this->resourceUrlResolver = $resolver;
        return $this;
    }

    /**
     * Resolve the label a related resource is shown as. Replaces the default
     * "name-like field, else identifier" heuristic.
     * @param \Closure $resolver function(RESTResource $resource): string
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
                if (!in_array($key, $columns, true)) {
                    $columns[] = $key;
                }

                $cells[$key] = $this->makeCell($value);
            }

            $rows[] = [
                'resource' => $resource,
                'cells' => $cells
            ];
        }

        $pagination = null;
        if ($this->currentUrl) {
            $pagination = new Pagination($this->resourceCollection, $this->currentUrl);
        }

        return new HtmlString(view('table::table', [
            'columns' => $columns,
            'rows' => $rows,
            'modelActions' => $this->modelActions,
            'collectionActions' => $this->collectionActions,
            'pagination' => $pagination
        ])->__toString());
    }

    /**
     * @param Value $value
     * @return Cell
     */
    protected function makeCell(Value $value)
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
     * Human readable label for a related resource: a name-like field when
     * the resource has one, its identifier otherwise.
     * @param RESTResource $resource
     * @return string
     */
    protected function getResourceLabel(RESTResource $resource)
    {
        if ($this->resourceLabelResolver) {
            return (string) call_user_func($this->resourceLabelResolver, $resource);
        }

        $values = $resource->toArray();
        foreach ([ 'name', 'title', 'label' ] as $candidate) {
            if (isset($values[$candidate]) && is_scalar($values[$candidate])) {
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
