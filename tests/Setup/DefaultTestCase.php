<?php

namespace SmurfWorks\ModelMeta\Tests\Setup;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use SmurfWorks\ModelMeta\ModelMeta;
use SmurfWorks\ModelMeta\ModelMetaServiceProvider;
use SmurfWorks\ModelMeta\Tests\Models\DummyModel;
use SmurfWorks\ModelMeta\Tests\Models\DummyModelWithPrefix;

class DefaultTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'SmurfWorks\\ModelMeta\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->setUpDatabase();

        \SmurfWorks\ModelMeta\ModelMeta::clearModelCache();
        \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            ModelMetaServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        cache()->clear();

        config()->set('model-meta.model-namespace', ['SmurfWorks\ModelMeta\Tests\Models']);
        config()->set('database.default', 'testing');

        $keysMigration = include __DIR__.'/../../database/migrations/create_model_meta_keys_table.php.stub';
        $keysMigration->up();

        $valuesMigration = include __DIR__.'/../../database/migrations/create_model_meta_values_table.php.stub';
        $valuesMigration->up();
    }

    protected function setUpDatabase()
    {
        foreach (['dummy_models', 'dummy_model_with_prefixes'] as $tableName) {
            $this->app['db']->connection()->getSchemaBuilder()->create(
                $tableName,
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('name');
                    $table->timestamps();
                }
            );
        }

        foreach (ModelMeta::storedValueTypes() as $type) {
            \SmurfWorks\ModelMeta\Models\ModelMetaKey::create([
                'key' => sprintf('dummy_models:registered_%s', $type),
                'model_type' => DummyModel::class,
                'store_value_as' => $type,
            ]);
        }

        foreach (ModelMeta::storedValueTypes() as $type) {
            \SmurfWorks\ModelMeta\Models\ModelMetaKey::create([
                'key' => sprintf('custom_prefix:registered_%s', $type),
                'model_type' => DummyModelWithPrefix::class,
                'store_value_as' => $type,
            ]);
        }

        collect(range(1, 5))
            ->each(fn (int $i) => DummyModel::create(['name' => sprintf('dummy-%d', $i)]));

        collect(range(1, 5))
            ->each(fn (int $i) => DummyModelWithPrefix::create(['name' => sprintf('dummy-%d', $i)]));
    }
}
