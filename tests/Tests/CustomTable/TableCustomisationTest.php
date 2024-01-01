<?php

use SmurfWorks\ModelMeta\Models\ModelMetaKey;
use SmurfWorks\ModelMeta\Models\ModelMetaValue;
use SmurfWorks\ModelMeta\Tests\Models\DummyModel;

it('supports custom table names on default classes', function () {
    expect((new ModelMetaKey)->getTable() === 'custom_keys_table');
    expect((new ModelMetaValue)->getTable() === 'custom_values_table');

    expect(ModelMetaKey::count())->toBeGreaterThan(3);

    $record = DummyModel::first();
    $record->update(['registered_string' => 'test']);
    expect($record->registered_string)->toBe('test')
        ->and(ModelMetaValue::count())->toBe(1);
});
