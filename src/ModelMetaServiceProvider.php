<?php

namespace SmurfWorks\ModelMeta;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModelMetaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-model-meta')
            ->hasConfigFile()
            ->hasMigration('create_model_meta_keys_table')
            ->hasMigration('create_model_meta_values_table')
            ->hasCommand(Commands\CreateKey::class)
            ->hasCommand(Commands\DeactivateKey::class)
            ->hasCommand(Commands\DeleteKey::class)
            ->hasCommand(Commands\ListKeys::class)
            ->hasCommand(Commands\ReactivateKey::class)
            ->hasCommand(Commands\UpdateKey::class);
    }
}
