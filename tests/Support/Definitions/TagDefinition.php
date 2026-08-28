<?php

namespace Tests\Support\Definitions;

use CatLab\Charon\Models\ResourceDefinition;
use Tests\Support\Models\Tag;

/**
 * Deliberately has no name-like field (name/title/label): exercises the label fallback.
 */
class TagDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(Tag::class);

        $this->identifier('id')->int();
        $this->field('slug')->string()->visible(true, true);
    }
}
