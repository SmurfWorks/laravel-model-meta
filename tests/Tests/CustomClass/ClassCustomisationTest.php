<?php

use SmurfWorks\ModelMeta\Tests\Models;

it('can use custom classes and tables', function () {

    $record = Models\DummyModel::first();
    $record->registered_string = 'test';
    $record->save();

    expect($record->registered_string)->toBe('test')
        ->and($record->metaValues()->count())->toBe(1)
        ->and($record->metaValues->first() instanceof Models\CustomValue)->toBeTrue()
        ->and($record->metaValues->first()->metaKey instanceof Models\CustomKey)->toBeTrue()
        ->and((new Models\CustomKey)->getTable())->toBe('custom_class_keys')
        ->and((new Models\CustomValue)->getTable())->toBe('custom_class_values');
});
