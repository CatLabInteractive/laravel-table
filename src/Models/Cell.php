<?php

namespace CatLab\Laravel\Table\Models;

/**
 * One rendered table cell: either a scalar value, a list of related
 * resources (each with a label and an optional url), or a structured
 * (array/object) value.
 */
class Cell
{
    const TYPE_SCALAR = 'scalar';
    const TYPE_RELATIONSHIP = 'relationship';
    const TYPE_OBJECT = 'object';

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var RelatedResource[]
     */
    private $related;

    /**
     * @param string $type
     * @param mixed $value
     * @param RelatedResource[] $related
     */
    private function __construct($type, $value = null, array $related = [])
    {
        $this->type = $type;
        $this->value = $value;
        $this->related = $related;
    }

    /**
     * @param mixed $value
     * @return Cell
     */
    public static function scalar($value)
    {
        return new self(self::TYPE_SCALAR, $value);
    }

    /**
     * @param RelatedResource[] $related
     * @return Cell
     */
    public static function relationship(array $related)
    {
        return new self(self::TYPE_RELATIONSHIP, null, $related);
    }

    /**
     * @param array $value
     * @return Cell
     */
    public static function object(array $value)
    {
        return new self(self::TYPE_OBJECT, $value);
    }

    /**
     * @return bool
     */
    public function isRelationship()
    {
        return $this->type === self::TYPE_RELATIONSHIP;
    }

    /**
     * @return bool
     */
    public function isObject()
    {
        return $this->type === self::TYPE_OBJECT;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return RelatedResource[]
     */
    public function getRelated()
    {
        return $this->related;
    }
}
