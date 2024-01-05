<?php

use SmurfWorks\ModelMeta\Models\ModelMetaKey;
use SmurfWorks\ModelMeta\Tests\Models\DummyModel;

/* STRING */

it('can assign registered string values', function () {

    $record = DummyModel::first();
    expect($record->registered_string)->toBeNull();

    $record->registered_string = 'test';
    $record->save();

    expect($record->registered_string)->toBe('test')
        ->and(is_string($record->registered_string))->toBeTrue();

    $record->fill(['registered_string' => 'test2'])->save();
    expect($record->registered_string)->toBe('test2');

    $record->update(['registered_string' => 'test3']);
    expect($record->registered_string)->toBe('test3');

    $refreshed = DummyModel::first();
    expect($refreshed->registered_string)->toBe('test3');
});

it('can set a default string', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_string')->update(['default_value' => 'a default string']);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_string === 'a default string')->toBeTrue();
});

/* BOOLEAN */

it('can assign registered boolean values', function () {

    $record = DummyModel::first();
    expect($record->registered_boolean)->toBeNull();

    $record->update(['registered_boolean' => true]);
    expect($record->registered_boolean)->toBeTrue()
        ->and(is_bool($record->registered_boolean))->toBeTrue();

    $refreshed = DummyModel::first();
    expect($refreshed->registered_boolean)->toBe(true)
        ->and(is_bool($refreshed->registered_boolean))->toBeTrue();
});

it('can set a default boolean', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_boolean')->update(['default_value' => true]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_boolean === true)->toBeTrue();

    ModelMetaKey::firstWhere('key', 'dummy_models:registered_boolean')->update(['default_value' => 'false']);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_boolean === false)->toBeTrue();
});

/* INTEGER */

it('can assign registered integer values', function () {

    $record = DummyModel::first();
    expect($record->registered_integer)->toBeNull();

    $record->update(['registered_integer' => '123']);
    expect($record->registered_integer)->toBe(123);

    $refreshed = DummyModel::first();
    expect($refreshed->registered_integer === 123)->toBeTrue();
});

it('can set a default integer', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_integer')->update(['default_value' => 777]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_integer === 777)->toBeTrue();
});

/* FLOAT */

it('can assign registered float values', function () {

    $record = DummyModel::first();
    expect($record->registered_float)->toBeNull();

    $record->update(['registered_float' => '123.05']);
    expect($record->registered_float)->toBe(123.05);

    $refreshed = DummyModel::first();
    expect($refreshed->registered_float === 123.05)->toBeTrue();
});

it('can set a default float', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_float')->update(['default_value' => 777.77]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_float === 777.77)->toBeTrue();
});

/* DATE */

it('can assign registered date values', function () {

    $record = DummyModel::first();
    expect($record->registered_date)->toBeNull();

    $record->update(['registered_date' => now()]);
    expect($record->registered_date instanceof \Carbon\Carbon)->toBeTrue();

    $refreshed = DummyModel::first();
    expect($refreshed->registered_date instanceof \Carbon\Carbon)->toBeTrue();
});

it('can set a default date', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_date')->update(['default_value' => now()]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_date instanceof \Carbon\Carbon)->toBeTrue();
});

/* TIME */

it('can assign registered time values', function () {

    $record = DummyModel::first();
    expect($record->registered_time)->toBeNull();

    $record->registered_time = '22:25:30';
    $record->save();

    expect($record->registered_time)->toBe('22:25:30')
        ->and(is_string($record->registered_time))->toBeTrue();
});

it('can set a default time', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_time')->update(['default_value' => '22:25:30']);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_time === '22:25:30')->toBeTrue();
});

/* DATETIME */

it('can assign registered datetime values', function () {

    $record = DummyModel::first();
    expect($record->registered_datetime)->toBeNull();

    $record->update(['registered_datetime' => now()]);
    expect($record->registered_datetime instanceof \Carbon\Carbon)->toBeTrue();

    $refreshed = DummyModel::first();
    expect($refreshed->registered_datetime instanceof \Carbon\Carbon)->toBeTrue();
});

it('can set a default datetime', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_datetime')->update([
        'default_value' => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '2010-02-01 13:35:20', 'UTC'),
    ]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_datetime instanceof \Carbon\Carbon)->toBeTrue();
    expect($record->registered_datetime->format('Y-m-d H:i:s'))->toBe('2010-02-01 13:35:20');
});

it('will respective defaults with timezone', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_datetime')->update([
        'default_value' => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '2010-02-01 13:35:20', 'Pacific/Auckland'),
    ]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_datetime instanceof \Carbon\Carbon)->toBeTrue();
    expect($record->registered_datetime->format('Y-m-d H:i:s'))->toBe('2010-02-01 13:35:20');
    expect($record->registered_datetime->getTimezone()->getName())->toBe('Pacific/Auckland');
});

/* TIMESTAMP */

it('can assign registered timestamp values', function () {

    $record = DummyModel::first();
    expect($record->registered_timestamp)->toBeNull();

    $record->update(['registered_timestamp' => '2024-01-01 12:00:00']);
    expect(is_int($record->registered_timestamp))->toBeTrue();

    $refreshed = DummyModel::first();
    expect(is_int($record->registered_timestamp))->toBeTrue();
});

it('can set a default timestamp', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_timestamp')->update([
        'default_value' => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '2010-02-01 13:35:20', 'UTC'),
    ]);

    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_timestamp instanceof \Carbon\Carbon)->toBeTrue();
    expect($record->registered_timestamp->format('Y-m-d H:i:s'))->toBe('2010-02-01 13:35:20');
});

/* JSON */

it('can assign registered json values', function () {

    $record = DummyModel::first();
    expect($record->registered_json)->toBeNull();

    $record->update(['registered_json' => ['test array']]);
    expect(is_array($record->registered_json))->toBeTrue();

    $record->update(['registered_json' => ['test key' => 'test value']]);
    expect(is_object($record->registered_json))->toBeTrue();

    $refreshed = DummyModel::first();
    expect(is_object($refreshed->registered_json))->toBeTrue();
});

it('can set a default json', function () {
    ModelMetaKey::firstWhere('key', 'dummy_models:registered_json')->update(['default_value' => (new \stdClass)]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect($record->registered_json instanceof \stdClass)->toBeTrue();

    ModelMetaKey::firstWhere('key', 'dummy_models:registered_json')->update(['default_value' => ['an array' => 'with assoc keys']]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect(is_array($record->registered_json))->toBeTrue();

    ModelMetaKey::firstWhere('key', 'dummy_models:registered_json')->update(['default_value' => ['just', 'numeric', 'keys']]);
    \SmurfWorks\ModelMeta\ModelMeta::clearSchemaCache(DummyModel::class);

    $record = DummyModel::first();
    expect(is_array($record->registered_json))->toBeTrue();
});

/* Other tests */

it('cannot assign unregistered keys', function () {

    $record = DummyModel::first();
    $record->unregistered_key = 'test';
    $record->save();
})->throws(\Illuminate\Database\QueryException::class);

it('can assign attributes before the model is created', function () {

    $record = DummyModel::create([
        'name' => 'New Model',
        'registered_string' => 'test',
    ]);

    expect($record->registered_string)->toBe('test');
});
