<?php

namespace SmurfWorks\ModelMeta\Commands;

use SmurfWorks\ModelMeta\Abstracts\KeyCommand;
use SmurfWorks\ModelMeta\ModelMeta;

class ListKeys extends KeyCommand
{
    public $signature = 'model-meta:list-keys';

    public $description = 'List the keys available for a specific model';

    public function handle(): int
    {
        try {
            $this->clearModelCache();
            $modelClass = $this->selectModel('Select a model to list the keys for');

            $records = ModelMeta::getKeyClass()::where('model_type', $modelClass)->get();
            if (! $records->count()) {
                throw new \RuntimeException('No keys are configured for the selected model.');
            }

            $this->table(
                ['Key', 'Store Value As', 'Description'],
                $records->map(fn ($record) => [
                    'key' => $record->original_key,
                    'stored_value_as' => $record->store_value_as,
                    'default_value' => (string) str($record->default_value)->limit(100),
                    'description' => $record->description,
                ])
            );

            return self::SUCCESS;

        } catch (\RuntimeException $error) {

            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
