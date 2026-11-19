<?php

namespace CatLab\Laravel\Table\Models;

/**
 * One input of the filter form: a filterable field, its current operator and
 * value and, for enum-like fields, the values it may take.
 *
 * The form sends one parameter per condition (see CatLab\Charon\Enums\FilterOperator):
 * ?name=value for "is", ?name[neq]=value, ?name[contains]=value,
 * ?name[empty]=1, ?name[not_empty]=1.
 */
class Filter
{
    /**
     * Operators (request names) that compare with nothing.
     */
    private const WITHOUT_VALUE = [ 'empty', 'not_empty' ];

    /**
     * @var string
     */
    private $operator = '';

    /**
     * @var string[] request names of the operators this filter offers
     */
    private $operators = [];

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
     * @var string|null
     */
    private $label;

    /**
     * @param string $name
     * @param string|null $value
     * @param string[]|null $options
     * @param string|null $label
     * @param string|null $operator request name of the current operator ('' or 'eq' is equality)
     * @param string[] $operators request names of the operators to offer
     */
    public function __construct(
        $name,
        $value = null,
        ?array $options = null,
        ?string $label = null,
        ?string $operator = null,
        array $operators = []
    ) {
        $this->operator = $operator === 'eq' ? '' : (string) $operator;
        $this->operators = $operators;
        $this->label = $label;
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
     * Human readable version of the name: "created_at" / "createdAt" -> "Created at".
     * @return string
     */
    public function getLabel()
    {
        if ($this->label !== null) {
            return $this->label;
        }

        $label = preg_replace('/(?<=[a-z0-9])([A-Z])/', ' $1', $this->name);
        $label = str_replace(['_', '-'], ' ', $label);
        return ucfirst(strtolower(trim($label)));
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if (!$this->takesValue()) {
            return true;
        }

        return $this->value !== null && $this->value !== '';
    }

    /**
     * Name of the value input for the current condition: the plain name for
     * "is", name[condition] otherwise.
     * @return string
     */
    public function getValueInputName()
    {
        return $this->getInputNameFor($this->operator);
    }

    /**
     * @param string $operator request name ('' is "is")
     * @return string
     */
    public function getInputNameFor($operator)
    {
        return $operator === '' ? $this->name : $this->name . '[' . $operator . ']';
    }

    /**
     * Request name of the current operator; '' is equality.
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return bool
     */
    public function hasOperators()
    {
        return count($this->operators) > 0;
    }

    /**
     * The operators to offer, request name ('' for equality) => label.
     * @return string[]
     */
    public function getOperators()
    {
        $out = [];
        foreach ($this->operators as $operator) {
            $out[$operator === 'eq' ? '' : $operator] = $this->getOperatorLabel($operator);
        }
        return $out;
    }

    /**
     * Whether the current operator compares with a value.
     * @return bool
     */
    public function takesValue()
    {
        return self::operatorTakesValue($this->operator);
    }

    /**
     * @param string $operator request name
     * @return bool
     */
    public static function operatorTakesValue($operator)
    {
        return !in_array($operator, self::WITHOUT_VALUE, true);
    }

    /**
     * @param string $operator
     * @return string
     */
    protected function getOperatorLabel($operator)
    {
        $key = 'table::table.operators.' . ($operator === '' ? 'eq' : $operator);
        $label = __($key);
        return $label === $key ? $operator : $label;
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
