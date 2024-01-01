<?php

use SmurfWorks\ModelMeta\Tests\Models\DummyModelWithPrefix;

it('can have custom prefixes', function () {

    $record = DummyModelWithPrefix::first();
    expect($record->registered_string)->toBeNull();

    $record->fill(['registered_string' => 'test'])->save();
    expect($record->registered_string)->toBe('test');

    expect($record->metaValues->first()->key)->toBe('custom_prefix:registered_string');
});

it('can exclude meta from fillable', function () {

    $record = DummyModelWithPrefix::first();
    expect($record->registered_boolean)->toBeNull();

    $record->update(['registered_boolean' => true]);
    expect($record->registered_boolean)->toBeNull();

    $record->registered_boolean = true;
    $record->save();
    expect($record->registered_boolean)->toBe(true);
});
