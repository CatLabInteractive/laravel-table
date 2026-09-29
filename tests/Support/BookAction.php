<?php

namespace Tests\Support;

use CatLab\Laravel\Table\Models\ModelAction;

/**
 * Reads the id from the charon resource the table hands to its actions.
 */
class BookAction extends ModelAction
{
    protected function getIdFromModel($model)
    {
        return ($model->getIdentifiers()->getValues())[0]->getValue();
    }
}
