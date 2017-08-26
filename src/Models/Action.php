<?php

namespace CatLab\Laravel\Table\Models;

/**
 * Class Action
 * @package CatLab\Laravel\Models
 */
abstract class Action
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var mixed[]
     */
    protected $routeParameters = [];

    /**
     * @var mixed[]
     */
    protected $queryParameters = [];

    /**
     * @var string
     */
    protected $label;

    /**
     * Action constructor.
     * @param $action
     * @param $label
     */
    public function __construct($action, $label)
    {
        $this->action = $action;
        $this->label = $label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param array $routeParameters
     * @return $this
     */
    public function setRouteParameters(array $routeParameters)
    {
        $this->routeParameters = $routeParameters;
        return $this;
    }

    /**
     * @param array $queryParameters
     * @return $this
     */
    public function setQueryParameters(array $queryParameters)
    {
        $this->queryParameters = $queryParameters;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}