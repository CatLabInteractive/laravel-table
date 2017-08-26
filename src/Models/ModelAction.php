<?php

namespace CatLab\Laravel\Table\Models;

/**
 * Class ModelAction
 * @package CatLab\Laravel\Table\Models
 */
class ModelAction extends Action
{
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
            'id' => $id
        ];

        if ($this->routeParameters !== null) {
            $routeParameters = array_merge($this->routeParameters, [ 'id' => $id ]);
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