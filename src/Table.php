<?php

namespace CatLab\Laravel\Table;

use CatLab\Charon\Collections\ResourceCollection;
use CatLab\Charon\Interfaces\Context;
use CatLab\Charon\Interfaces\ResourceDefinition;
use CatLab\Laravel\Table\Models\Action;
use CatLab\Laravel\Table\Models\CollectionAction;
use CatLab\Laravel\Table\Models\ModelAction;
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
     * Table constructor.
     * @param ResourceCollection $collection
     * @param ResourceDefinition $definition
     * @param Context $context
     */
    public function __construct(
        ResourceCollection $collection,
        ResourceDefinition $definition,
        Context $context
    ) {
        $this->resourceCollection = $collection;
        $this->definition = $definition;
        $this->context = $context;
        $this->modelActions = [];
        $this->collectionActions = [];
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
        $firstItem = $this->resourceCollection->first();
        if ($firstItem) {
            $columns = array_keys($firstItem->toArray());

            $resources = [];
            foreach ($this->resourceCollection as $v) {
                $resources[] = $v;
            }
        } else {
            $columns = [];
            $resources = [];
        }

        return new HtmlString(view('table::table', [
            'columns' => $columns,
            'resources' => $resources,
            'modelActions' => $this->modelActions,
            'collectionActions' => $this->collectionActions
        ])->__toString());
    }
}