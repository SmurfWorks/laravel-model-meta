<?php

namespace SmurfWorks\ModelMeta\Traits;

use SmurfWorks\ModelMeta\ModelMeta;

trait ModelMetaSeeder
{
    /**
     * Seed the registration of a meta key.
     */
    public function createMetaKey(
        string $modelClass,
        string $key,
        string $type,
        ?string $description = null,
        mixed $default = null
    ): self {
        /* Check the model is valid */
        if (! class_exists($modelClass) || ! in_array(HasModelMeta::class, class_uses($modelClass))) {
            throw new \InvalidArgumentException('The given model class does not use the HasModelMeta trait.');
        }

        /* Make sure the key doesn't use reserved characters */
        $key = strtolower(str_replace(':', '', $key));

        /* Make sure the key has a value after sanitization */
        if (! $key) {
            throw new \InvalidArgumentException('You must enter a valid key name.');
        }

        /* Make sure the key isn't reserved */
        if (in_array($key, ModelMeta::getModelSchema($modelClass)['reserved'])) {
            throw new \InvalidArgumentException('That key is reserved and cannot be used.');
        }

        if (! in_array($type, ModelMeta::storedValueTypes())) {
            throw new \InvalidArgumentException('The given type is not a valid value type.');
        }

        $record = ModelMeta::getKeyClass()::firstOrNew([
            'key' => ModelMeta::getFQMetaKey($modelClass, $key),
            'model_type' => $modelClass,
        ]);

        $record->fill([
            'store_value_as' => $type,
            'description' => $description,
            'default_value' => $default,
        ]);

        $record->save();

        /* Clear the model schema cache */
        ModelMeta::clearSchemaCache($modelClass);

        return $this;
    }
}
