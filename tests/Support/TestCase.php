<?php

namespace Tests\Support;

use CatLab\Charon\Collections\ResourceCollection;
use CatLab\Charon\Enums\Action;
use CatLab\Charon\Models\Context;
use CatLab\Laravel\Table\TableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Tests\Support\Definitions\BookDefinition;

/**
 * Testbench base: registers the table view namespace and offers a helper
 * that turns plain PHP fixture objects into a charon ResourceCollection
 * through charon's framework-agnostic ResourceTransformer, so no database
 * is needed to exercise Table::render().
 */
abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }

    protected function indexContext(): Context
    {
        return new Context(Action::INDEX, []);
    }

    /**
     * @param object[] $entities
     * @param string $definition
     * @return ResourceCollection
     */
    protected function toCollection(array $entities, string $definition = BookDefinition::class): ResourceCollection
    {
        $transformer = new PlainResourceTransformer(null, null, null, new ArrayQueryAdapter());
        return $transformer->toResources($definition, $entities, $this->indexContext());
    }
}
