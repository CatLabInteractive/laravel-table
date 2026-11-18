<?php

namespace Tests\Support\Definitions;

/**
 * A book whose status has a label of its own.
 */
class LabelledBookDefinition extends BookDefinition
{
    public function __construct()
    {
        parent::__construct();

        $this->getFields()->getFromDisplayName('status')->label('Stage');
    }
}
