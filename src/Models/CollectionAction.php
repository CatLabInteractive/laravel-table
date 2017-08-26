<?php

namespace CatLab\Laravel\Table\Models;

/**
 * Class CollectionAction
 * @package CatLab\Laravel\Table\Models
 */
class CollectionAction extends Action
{
    /**
     * Get the URL
     * @return string
     */
    public function getUrl()
    {
        $routeParameters = [];

        if ($this->routeParameters !== null) {
            $routeParameters = $this->routeParameters;
        }

        $routeParameters = array_merge($routeParameters, $this->queryParameters);

        return action($this->action, $routeParameters);
    }
}