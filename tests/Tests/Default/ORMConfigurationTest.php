<?php

use SmurfWorks\ModelMeta\Models\ModelMetaKey;
use SmurfWorks\ModelMeta\Models\ModelMetaValue;
use SmurfWorks\ModelMeta\Tests\Models\DummyModel;

it('has relations configured', function () {

    /* Assert no value has been set for this meta field yet */
    $record = DummyModel::first();
    expect($record->registered_string)->toBeNull()
        ->and($record->metaValues()->count())->toBe(0);

    /* Make sure we have a value to work with */
    $record->fill(['registered_string' => 'test'])->save();
    expect($record->registered_string)->toBe('test')
        ->and($record->metaValues()->count())->toBe(1);

    $valueRecord = ModelMetaValue::first();
    $keyRecord = $valueRecord->metaKey;

    expect($valueRecord->key)->toBe('dummy_models:registered_string')
        ->and($keyRecord instanceof ModelMetaKey)->toBeTrue()
        ->and($keyRecord->metaValues->count())->toBe(1)
        ->and($keyRecord->metaValues instanceof \Illuminate\Database\Eloquent\Collection)->toBeTrue()
        ->and($valueRecord->model instanceof DummyModel)->toBeTrue();
});
