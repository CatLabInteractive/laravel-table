<?php

namespace CatLab\Laravel\Table;

use CatLab\Charon\Collections\ResourceCollection;
use CatLab\Charon\Interfaces\Context;
use CatLab\Charon\Interfaces\ResourceDefinition;
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
     * @var string
     */
    private $modelActions;

    /**
     * @var string
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
    }

    /**
     * @param $action
     * @param $routeParameters
     * @param $queryParameters
     * @param $label
     */
    public function modelAction($action, $routeParameters, $queryParameters, $label)
    {
        $this->modelActions[] = [
            'action' => $action,
            'routeParameters' => $routeParameters,
            'queryParameters' => $queryParameters,
            'label' => $label
        ];
    }

    /**
     * @param $action
     * @param $routeParameters
     * @param $queryParameters
     * @param $label
     * @internal param $parameters
     */
    public function collectionAction($action, $routeParameters, $queryParameters, $label)
    {
        $this->collectionActions[] = [
            'action' => $action,
            'routeParameters' => $routeParameters,
            'queryParameters' => $queryParameters,
            'label' => $label
        ];
    }
    /**
     * @return HtmlString
     */
    public function render()
    {
        $firstItem = $this->resourceCollection->first();
        if (!$firstItem) {
            return '<p>No content.</p>';
        }

        $columns = array_keys($firstItem->toArray());

        $resources = [];
        foreach ($this->resourceCollection as $v) {
            $resources[] = $v;
        }

        return new HtmlString(view('table::table', [
            'columns' => $columns,
            'resources' => $resources,
            'modelActions' => $this->modelActions,
            'collectionActions' => $this->collectionActions
        ])->__toString());
    }
}