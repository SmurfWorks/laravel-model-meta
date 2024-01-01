<?php

use SmurfWorks\ModelMeta\Models;
use SmurfWorks\ModelMeta\Tests\Models as TestModels;

it('can create new keys', function () {
    $this->artisan('model-meta:create-key')
        ->expectsQuestion('What model will utilize this meta key?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What is the new key name? (Colon characters will be removed)', 'newly_created_string')
        ->expectsQuestion('What is the value type for this key?', 'string')
        ->expectsQuestion('Define a default value (leave blank for null)', '')
        ->expectsQuestion(
            'Optionally enter a short description to explain the values of this meta key',
            'This is input by the test'
        )
        ->assertSuccessful();

    expect(Models\ModelMetaKey::where('key', 'dummy_models:newly_created_string')->exists())->toBeTrue();

    $this->artisan('model-meta:create-key')
        ->expectsQuestion('What model will utilize this meta key?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What is the new key name? (Colon characters will be removed)', ':::')
        ->assertFailed();
});

it('can deactivate and reactivate keys', function () {

    $record = TestModels\DummyModel::first();
    $record->registered_string = 'test string';
    $record->save();

    expect($record->metaValues()->where('key', 'dummy_models:registered_string')->exists())->toBeTrue();

    $this->artisan('model-meta:deactivate-key')
        ->expectsQuestion('What model contains the key you want to deactivate?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to deactivate?', 'registered_string')
        ->expectsConfirmation('The key is ready to deactivate, continue?', 'yes')
        ->assertSuccessful();

    expect(Models\ModelMetaKey::where('key', 'dummy_models:registered_string')->exists())->toBeFalse()
        ->and(Models\ModelMetaKey::withTrashed()->where('key', 'dummy_models:registered_string')->exists())->toBeTrue()
        ->and($record->metaValues()->where('key', 'dummy_models:registered_string')->exists())->toBeTrue();

    $this->artisan('model-meta:reactivate-key')
        ->expectsQuestion('What model contains the key you want to reactivate?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to reactivate?', 'registered_string')
        ->expectsConfirmation('The key is ready to reactivate, continue?', 'yes')
        ->assertSuccessful();

    expect(Models\ModelMetaKey::where('key', 'dummy_models:registered_string')->exists())->toBeTrue();
});

it('can update key value types', function () {
    $this->artisan('model-meta:update-key')
        ->expectsQuestion('What model contains the key that you would like to update?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('Which key would you like to update?', 'registered_string')
        ->expectsConfirmation('Would you like to change the value type?', 'yes')
        ->expectsQuestion('Please select the new value type', 'boolean')
        ->expectsConfirmation('Would you like to update the default value?', 'yes')
        ->expectsQuestion('Please enter your new default value', 'true')
        ->expectsConfirmation('Would you like to update the description?', 'yes')
        ->expectsQuestion('Please enter your new description', 'An updated description')
        ->assertSuccessful();

    $updatedRecord = Models\ModelMetaKey::where('key', 'dummy_models:registered_string')->first();
    expect($updatedRecord->store_value_as)->toBe('boolean')
        ->and($updatedRecord->default_value)->toBe(true)
        ->and($updatedRecord->description)->toBe('An updated description');
});

it('can delete keys and cascade values', function () {

    $record = TestModels\DummyModel::first();
    $record->registered_string = 'test string';
    $record->save();

    expect($record->metaValues()->where('key', 'dummy_models:registered_string')->exists())->toBeTrue();

    $this->artisan('model-meta:delete-key')
        ->expectsQuestion('What model contains the key you want to delete?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to delete?', 'registered_string')
        ->expectsQuestion('Type the name of the key to confirm deletion - incorrect values will cancel the action', 'registered_string')
        ->assertSuccessful();

    $refreshedRecord = TestModels\DummyModel::first();

    expect(Models\ModelMetaKey::withTrashed()->where('key', 'dummy_models:registered_string')->exists())->toBeFalse()
        ->and(Models\ModelMetaValue::where('key', 'dummy_models:registered_string')->exists())->toBeFalse();
});

it('can list registered keys for a model', function () {

    $this->artisan('model-meta:list-keys')
        ->expectsQuestion('Select a model to list the keys for', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->assertSuccessful();
});

/* Exception tests below */

it('cant use reserved keywords', function () {

    $this->artisan('model-meta:create-key')
        ->expectsQuestion('What model will utilize this meta key?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What is the new key name? (Colon characters will be removed)', 'created_at')
        ->assertFailed();
});

it('wont work without keys', function () {

    \SmurfWorks\ModelMeta\Models\ModelMetaKey::query()
        ->where('model_type', TestModels\DummyModel::class)
        ->forceDelete();

    $this->artisan('model-meta:update-key')
        ->expectsQuestion('What model contains the key that you would like to update?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->assertFailed();
});

it('wont create duplicates for the same model', function () {

    $this->artisan('model-meta:create-key')
        ->expectsQuestion('What model will utilize this meta key?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What is the new key name? (Colon characters will be removed)', 'registered_string')
        ->assertFailed();
});

it('wont update a deactivated model', function () {

    Models\ModelMetaKey::where('model_type', TestModels\DummyModel::class)
        ->where('key', 'registered_string')
        ->delete(); // Soft deleted

    cache()->clear();

    $this->artisan('model-meta:update-key')
        ->expectsQuestion('What model contains the key that you would like to update?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('Which key would you like to update?', 'registered_string')
        ->assertFailed();
});

it('wont update when no updates specified', function () {

    $this->artisan('model-meta:update-key')
        ->expectsQuestion('What model contains the key that you would like to update?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('Which key would you like to update?', 'registered_string')
        ->expectsConfirmation('Would you like to change the value type?', 'no')
        ->expectsConfirmation('Would you like to update the default value?', 'no')
        ->expectsConfirmation('Would you like to update the description?', 'no')
        ->assertFailed();
});

it('wont deactivate when cancelled', function () {

    $this->artisan('model-meta:deactivate-key')
        ->expectsQuestion('What model contains the key you want to deactivate?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to deactivate?', 'registered_string')
        ->expectsConfirmation('The key is ready to deactivate, continue?', 'no')
        ->assertFailed();

    expect(Models\ModelMetaKey::where('key', 'dummy_models:registered_string')->exists())->toBeTrue();
});

it('wont reactivate when cancelled', function () {

    $this->artisan('model-meta:deactivate-key')
        ->expectsQuestion('What model contains the key you want to deactivate?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to deactivate?', 'registered_string')
        ->expectsConfirmation('The key is ready to deactivate, continue?', 'yes')
        ->assertSuccessful();

    $this->artisan('model-meta:reactivate-key')
        ->expectsQuestion('What model contains the key you want to reactivate?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to reactivate?', 'registered_string')
        ->expectsConfirmation('The key is ready to reactivate, continue?', 'no')
        ->assertFailed();

    expect(Models\ModelMetaKey::where('key', 'dummy_models:registered_string')->exists())->toBeFalse();
});

it('wont delete when cancelled', function () {

    $this->artisan('model-meta:delete-key')
        ->expectsQuestion('What model contains the key you want to delete?', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->expectsQuestion('What key would you like to delete?', 'registered_string')
        ->expectsQuestion('Type the name of the key to confirm deletion - incorrect values will cancel the action', 'cancel')
        ->assertFailed();

    expect(Models\ModelMetaKey::where('key', 'dummy_models:registered_string')->exists())->toBeTrue();
});

it('wont list when there are no keys', function () {

    Models\ModelMetaKey::where('model_type', TestModels\DummyModel::class)->forceDelete();

    $this->artisan('model-meta:list-keys')
        ->expectsQuestion('Select a model to list the keys for', 'SmurfWorks\ModelMeta\Tests\Models\DummyModel')
        ->assertFailed();
});
