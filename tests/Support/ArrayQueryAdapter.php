<?php

namespace Tests\Support;

use CatLab\Base\Enum\Operator;
use CatLab\Base\Models\Database\OrderParameter;
use CatLab\Charon\Interfaces\Context;
use CatLab\Charon\Interfaces\ResourceDefinition;
use CatLab\Charon\Interfaces\ResourceTransformer;
use CatLab\Charon\Models\Identifier;
use CatLab\Charon\Models\Properties\Base\Field;
use CatLab\Charon\Models\Properties\RelationshipField;
use CatLab\Charon\Resolvers\QueryAdapter;

/**
 * For plain PHP fixtures a "query builder" is simply the array of related
 * entities; fetching its records means handing that array back. Nothing
 * else (limits, filters, sorting) is needed to render a table.
 */
class ArrayQueryAdapter extends QueryAdapter
{
    public function getRecords(ResourceTransformer $transformer, ResourceDefinition $definition, Context $context, $queryBuilder)
    {
        return $queryBuilder;
    }

    public function countRecords(ResourceTransformer $transformer, ResourceDefinition $definition, Context $context, $queryBuilder)
    {
        return is_countable($queryBuilder) ? count($queryBuilder) : 0;
    }

    public function getQualifiedName(Field $field): string
    {
        return $field->getName();
    }

    public function getChildByIdentifiers(ResourceTransformer $transformer, RelationshipField $field, $parentEntity, Identifier $identifier, Context $context): void
    {
    }

    public function applyLimit(ResourceTransformer $transformer, ResourceDefinition $definition, Context $context, $queryBuilder, $records, $skip): void
    {
    }

    protected function applySimpleWhere(ResourceTransformer $transformer, ResourceDefinition $definition, Context $context, Field $field, $queryBuilder, $value, $operator = Operator::EQ)
    {
    }

    protected function applySimpleSorting(ResourceTransformer $transformer, ResourceDefinition $definition, Context $context, Field $field, $queryBuilder, $direction = OrderParameter::ASC)
    {
    }
}
