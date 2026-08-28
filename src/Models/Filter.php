<?php

namespace CatLab\Laravel\Table\Models;

/**
 * One input of the filter form: a filterable field, its current value and,
 * for enum-like fields, the values it may take.
 */
class Filter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var string[]|null
     */
    private $options;

    /**
     * @param string $name
     * @param string|null $value
     * @param string[]|null $options
     */
    public function __construct($name, $value = null, ?array $options = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function hasOptions()
    {
        return $this->options !== null && count($this->options) > 0;
    }

    /**
     * @return string[]
     */
    public function getOptions()
    {
        return $this->options ?? [];
    }
}
