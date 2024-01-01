<?php

use SmurfWorks\ModelMeta\Tests\Models\DummyModel;

it('will add the attribute to the result', function () {

    $record = DummyModel::first();
    $record->registered_string = 'test';
    $record->save();

    $result = DummyModel::withMeta('registered_string')->first()->toArray();

    expect($result)->toHaveKey('name');
    expect($result)->toHaveKey('registered_string');
    expect(in_array('registered_boolean', array_keys($result)))->toBeFalse();

    $result = DummyModel::withMeta('registered_string')->withMeta('registered_boolean')->first()->toArray();

    expect($result)->toHaveKey('name');
    expect($result)->toHaveKey('registered_string');
    expect($result)->toHaveKey('registered_boolean');
});
