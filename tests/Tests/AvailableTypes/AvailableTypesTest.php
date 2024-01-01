<?php

use Illuminate\Support\Facades\DB;
use SmurfWorks\ModelMeta\ModelMeta;

it('will not migrate disabled types', function () {

    $columns = DB::getSchemaBuilder()->getColumnListing((new \SmurfWorks\ModelMeta\Models\ModelMetaValue)->getTable());

    expect(in_array(ModelMeta::TYPE_FULLTEXT, $columns))->toBeFalse()
        ->and(in_array(ModelMeta::TYPE_JSONB, $columns))->toBeFalse()
        ->and(in_array(ModelMeta::TYPE_INTEGER, $columns))->toBeFalse()
        ->and(in_array(ModelMeta::TYPE_STRING, $columns))->toBeTrue()
        ->and(in_array(ModelMeta::TYPE_JSON, $columns))->toBeTrue()
        ->and(in_array(ModelMeta::TYPE_DATETIME, $columns))->toBeTrue();
});

it('will not return disabled types', function () {

    $availableTypes = ModelMeta::storedValueTypes();

    expect(in_array(ModelMeta::TYPE_FULLTEXT, $availableTypes))->toBeFalse()
        ->and(in_array(ModelMeta::TYPE_JSONB, $availableTypes))->toBeFalse()
        ->and(in_array(ModelMeta::TYPE_INTEGER, $availableTypes))->toBeFalse()
        ->and(in_array(ModelMeta::TYPE_STRING, $availableTypes))->toBeTrue()
        ->and(in_array(ModelMeta::TYPE_JSON, $availableTypes))->toBeTrue()
        ->and(in_array(ModelMeta::TYPE_DATETIME, $availableTypes))->toBeTrue();
});
