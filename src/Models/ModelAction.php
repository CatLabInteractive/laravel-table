<?php

namespace CatLab\Laravel\Table\Models;

/**
 * Class ModelAction
 * @package CatLab\Laravel\Table\Models
 */
class ModelAction extends Action
{
    /**
     * @var string
     */
    private $idParameter;

    /**
     * @param string $action
     * @param string $label
     * @param string $idParameter
     */
    public function __construct($action, $label, $idParameter = 'id')
    {
        parent::__construct($action, $label);
        $this->idParameter = $idParameter;
    }

    /**
     * @var \Closure
     */
    protected $condition;

    /**
     * Get the URL
     * @param $model
     * @return string
     */
    public function getUrl($model): string
    {
        $id = $this->getIdFromModel($model);

        $routeParameters = [
            $this->idParameter => $id
        ];

        if ($this->routeParameters !== null) {
            $routeParameters = array_merge($this->routeParameters, [ $this->idParameter => $id ]);
        }

        $routeParameters = array_merge($routeParameters, $this->queryParameters);

        return action($this->action, $routeParameters);
    }

    /**
     * @param \Closure $condition
     * @return $this
     */
    public function setCondition(\Closure $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Should show?
     * @param $model
     * @return bool
     */
    public function shouldShow($model)
    {
        if (isset($this->condition)) {
            return call_user_func($this->condition, $model);
        }
        return true;
    }

    /**
     * @param $model
     * @return mixed
     */
    protected function getIdFromModel($model)
    {
        return $model->id;
    }
}
