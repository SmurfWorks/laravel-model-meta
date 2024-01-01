<?php

use SmurfWorks\ModelMeta\Tests\Models\DummyModel;

it('can migrate values to another field', function () {

    $record = DummyModel::first();
    $record->registered_string = 'my string';
    $record->save();

    expect($record->registered_string)->toBe('my string')
        ->and($record->metaValues()->firstWhere('key', 'dummy_models:registered_string')->value)->toBe('my string');

    /* Migrate the value to be a new schema column */
    $migration = include sprintf('%s/../../Setup/add_registered_string_column.php', __DIR__);
    $migration->up();

    /* Move the values */
    \SmurfWorks\ModelMeta\ModelMeta::migrateValues(
        DummyModel::class,
        'registered_string',
        fn ($model, $value) => $model->setAttribute('registered_string', $value)->save()
    );

    $this->artisan('model-meta:delete-key')
        ->expectsQuestion('What model contains the key you want to delete?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to delete?', 'registered_string')
        ->expectsQuestion('Type the name of the key to confirm deletion - incorrect values will cancel the action', 'registered_string')
        ->assertSuccessful();

    $refreshedRecord = DummyModel::first();
    expect($refreshedRecord->registered_string)->toBe('my string')
        ->and($refreshedRecord->metaValues()->firstWhere('key', 'dummy_models:registered_string'))->toBeNull();
});
