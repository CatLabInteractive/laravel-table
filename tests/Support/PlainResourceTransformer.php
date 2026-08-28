<?php

namespace Tests\Support;

use CatLab\Charon\ResourceTransformer;

/**
 * charon's ResourceTransformer is abstract only to force each framework
 * binding to name its concrete class; the defaults it ships (Simple* resolvers)
 * already handle plain PHP objects, which is all these tests need.
 */
class PlainResourceTransformer extends ResourceTransformer
{
}
