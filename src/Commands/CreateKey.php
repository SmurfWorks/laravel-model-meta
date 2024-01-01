<?php

namespace SmurfWorks\ModelMeta\Commands;

use SmurfWorks\ModelMeta\Abstracts\KeyCommand;
use SmurfWorks\ModelMeta\ModelMeta;

class CreateKey extends KeyCommand
{
    public $signature = 'model-meta:create-key';

    public $description = 'Create a new meta key for a model';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->clearModelCache();
            $modelClass = $this->selectModel('What model will utilize this meta key?');

            $key = strtolower(
                str_replace(
                    ':',
                    '',
                    $this->ask('What is the new key name? (Colon characters will be removed)')
                )
            );

            if (! $key) {
                throw new \RuntimeException('You must enter a valid key name.');
            }

            if (in_array($key, ModelMeta::getModelSchema($modelClass)['reserved'])) {
                throw new \RuntimeException('That key is reserved and cannot be used.');
            }

            if ((bool) $this->findKey($modelClass, $key, fn ($q) => $q->withTrashed())) {
                throw new \RuntimeException('That key already exists for the selected model.');
            }

            $valueType = $this->selectValueType('What is the value type for this key?');
            $defaultValue = $this->ask('Define a default value (leave blank for null)');
            $description = $this->ask('Optionally enter a short description to explain the values of this meta key');

            /* Create the meta key */
            ModelMeta::getKeyClass()::create([

                'key' => ModelMeta::getFQMetaKey($modelClass, $key),
                'description' => $description,
                'model_type' => $modelClass,
                'store_value_as' => $valueType,
                'default_value' => $defaultValue,
            ]);

            $this->clearSchemaCache($modelClass);

            $this->comment(sprintf('Meta Key "%s" has been created for Model Class "%s". Happy developing!', $key, $modelClass));

            return self::SUCCESS;

        } catch (\RuntimeException $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
