<?php

use SmurfWorks\ModelMeta\ModelMeta;
use SmurfWorks\ModelMeta\Tests\Models;
use SmurfWorks\ModelMeta\Tests\Seeders\DummyModelSeeder;

it('will seed new keys', function () {
    $this->artisan('db:seed', ['--class' => DummyModelSeeder::class]);

    $record = Models\DummyModel::where('name', 'Seeded Test')->with('metaValues')->first();

    expect($record instanceof Models\DummyModel)->toBeTrue()
        ->and(ModelMeta::getModelSchema(Models\DummyModel::class)['keys'])
        ->toHaveKeys(['seeded_string', 'seeded_boolean', 'seeded_integer'])
        ->and($record->seeded_string)->toBe('Test String')
        ->and($record->seeded_boolean)->toBe(true)
        ->and($record->seeded_integer)->toBe(123);
});

it('will update an existing key', function () {
    $seeder = (new DummyModelSeeder);
    $seeder->createMetaKey(
        Models\DummyModel::class,
        'registered_string',
        ModelMeta::TYPE_INTEGER,
        'New Description'
    );

    $record = ModelMeta::getKeyClass()::where('key', 'dummy_models:registered_string')->first();
    expect($record->store_value_as)->toBe(ModelMeta::TYPE_INTEGER)
        ->and($record->description)->toBe('New Description');
});

it('requires a valid model class', function () {
    $seeder = (new DummyModelSeeder);
    $seeder->createMetaKey(\App\Models\User::class, 'some_new_key', ModelMeta::TYPE_STRING);
})->expectException(\InvalidArgumentException::class);

it('requires a model class with the trait', function () {
    $seeder = (new DummyModelSeeder);
    $seeder->createMetaKey(Models\UnassignedModel::class, 'some_new_key', ModelMeta::TYPE_STRING);
})->expectException(\InvalidArgumentException::class);

it('requires a valid key name', function () {
    $seeder = (new DummyModelSeeder);
    $seeder->createMetaKey(Models\DummyModel::class, ':::', ModelMeta::TYPE_STRING);
})->expectException(\InvalidArgumentException::class);

it('requires an unreserved key name', function () {
    $seeder = (new DummyModelSeeder);
    $seeder->createMetaKey(Models\DummyModel::class, 'created_at', ModelMeta::TYPE_STRING);
})->expectException(\InvalidArgumentException::class);

it('requires a valid, registered value type', function () {
    $seeder = (new DummyModelSeeder);
    $seeder->createMetaKey(Models\DummyModel::class, 'registered_jsonb', ModelMeta::TYPE_JSONB);
})->expectException(\InvalidArgumentException::class);
