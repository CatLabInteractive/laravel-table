<?php

namespace CatLab\Laravel\Table;

use CatLab\Charon\Collections\ResourceCollection;
use CatLab\Charon\Interfaces\Context;
use CatLab\Charon\Interfaces\ResourceDefinition;
use CatLab\Laravel\Table\Models\Action;
use CatLab\Laravel\Table\Models\CollectionAction;
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
     * @return HtmlString
     */
    public function render()
    {
        // Columns are the union of every resource's keys, in first-seen
        // order: a polymorphic collection (e.g. workflow steps of different
        // types) does not share one key set, so the first item alone would
        // leave later rows with too few or misaligned cells.
        $columns = [];
        $resources = [];
        foreach ($this->resourceCollection as $v) {
            $resources[] = $v;
            foreach (array_keys($v->toArray()) as $key) {
                if (!in_array($key, $columns, true)) {
                    $columns[] = $key;
                }
            }
        }

        $pagination = null;
        if ($this->currentUrl) {
            $pagination = new Pagination($this->resourceCollection, $this->currentUrl);
        }

        return new HtmlString(view('table::table', [
            'columns' => $columns,
            'resources' => $resources,
            'modelActions' => $this->modelActions,
            'collectionActions' => $this->collectionActions,
            'pagination' => $pagination
        ])->__toString());
    }
}
