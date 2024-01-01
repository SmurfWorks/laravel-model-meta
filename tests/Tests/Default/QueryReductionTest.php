<?php

use Illuminate\Support\Facades\DB;
use SmurfWorks\ModelMeta\Tests\Models\DummyModel;

it('will not requery after insert or update', function () {

    $record = DummyModel::with('metaValues')->first();

    DB::enableQueryLog();

    $record->registered_string = 'test'; // No query
    $record->save(); // One save query (1)

    $record->update([
        'name' => 'test record',
        'registered_string' => 'test2',
    ]); // Two save queries (3)

    expect($record->name)->toBe('test record')
        ->and($record->registered_string)->toBe('test2');

    $record->update(['name' => 'test record name']); // One save query (4)
    expect($record->name)->toBe('test record name');

    $record->update([
        'registered_string' => 'test 3',
        'registered_boolean' => true,
    ]); // Two save queries (6)

    expect($record->registered_string)->toBe('test 3')
        ->and($record->registered_boolean)->toBe(true)
        ->and(DB::getQueryLog())->toHaveCount(6);
});
