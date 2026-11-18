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
     * @var string|null
     */
    protected $icon;

    /**
     * @var bool
     */
    protected $labelled = false;

    /**
     * @var string|null
     */
    protected $style;

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string|null
     */
    protected $confirm;

    /**
     * @var string|null
     */
    protected $optionsName;

    /**
     * @var \Closure|null
     */
    protected $options;

    /**
     * @var \Closure|null
     */
    protected $selectedOption;

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
     * Show this action as an icon button next to the row instead of in the
     * row's dropdown menu. The name picks the view table::icons.{name}; the
     * package ships 'view', 'edit' and 'delete', and an application adds its
     * own in resources/views/vendor/table/icons.
     * @param string|null $icon
     * @return $this
     */
    public function setIcon(?string $icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
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

    /**
     * Draw the action as a button with its label next to the row, instead
     * of an icon (setIcon) or an entry of the row's menu.
     * @param bool $labelled
     * @return $this
     */
    public function showLabel(bool $labelled = true)
    {
        $this->labelled = $labelled;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLabelButton(): bool
    {
        return $this->labelled;
    }

    /**
     * Next to the row (an icon or a labelled button), or in its menu.
     * @return bool
     */
    public function isButton(): bool
    {
        return $this->labelled || $this->icon !== null;
    }

    /**
     * Bootstrap button style of a labelled button or an icon ("primary",
     * "danger", "outline-secondary"). Without one an icon is outline-secondary
     * (the delete icon outline-danger) and a labelled button secondary.
     * @param string|null $style
     * @return $this
     */
    public function setStyle(?string $style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        if ($this->style !== null) {
            return $this->style;
        }

        if ($this->icon === 'delete') {
            return 'outline-danger';
        }

        return $this->icon !== null && !$this->labelled ? 'outline-secondary' : 'secondary';
    }

    /**
     * Do the action with a form instead of a link: "post", "put", "patch"
     * or "delete" (sent as a post with a _method field, and a csrf token).
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = strtolower($method);
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    public function isForm(): bool
    {
        return $this->method !== 'get';
    }

    /**
     * Ask this before the action is done (a browser confirm).
     * @param string|null $question
     * @return $this
     */
    public function setConfirm(?string $question)
    {
        $this->confirm = $question;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConfirm(): ?string
    {
        return $this->confirm;
    }

    /**
     * Put a select in front of the button of a form action, posted as
     * $name: e.g. choose a template and "Use". Rows the closure gives no
     * options for get no select.
     * @param string $name
     * @param \Closure $options function ($model): array value => label
     * @param \Closure|null $selected function ($model): mixed the value to preselect
     * @return $this
     */
    public function setOptions(string $name, \Closure $options, ?\Closure $selected = null)
    {
        $this->optionsName = $name;
        $this->options = $options;
        $this->selectedOption = $selected;

        if ($this->method === 'get') {
            $this->method = 'post';
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOptionsName(): ?string
    {
        return $this->optionsName;
    }

    /**
     * @param mixed $model
     * @return array value => label
     */
    public function getOptions($model): array
    {
        return $this->options ? (array) call_user_func($this->options, $model) : [];
    }

    /**
     * @param mixed $model
     * @return string|null
     */
    public function getSelectedOption($model): ?string
    {
        if (!$this->selectedOption) {
            return null;
        }

        $selected = call_user_func($this->selectedOption, $model);
        return $selected === null ? null : (string) $selected;
    }
}
