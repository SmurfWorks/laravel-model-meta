<?php

namespace SmurfWorks\ModelMeta\Commands;

use SmurfWorks\ModelMeta\Abstracts\KeyCommand;

class DeactivateKey extends KeyCommand
{
    public $signature = 'model-meta:deactivate-key';

    public $description = 'Deactivate an existing meta key for a model without removing existing values';

    public function handle(): int
    {
        try {
            $this->clearModelCache();
            $modelClass = $this->selectModel('What model contains the key you want to deactivate?');
            $key = $this->selectKey($modelClass, 'What key would you like to deactivate?');

            /* selectKey will validate this exists */
            $record = $this->findKey($modelClass, $key);

            if (! $this->confirm('The key is ready to deactivate, continue?')) {
                throw new \RuntimeException('Deactivation has been cancelled.');
            }

            $record->delete();
            $this->clearSchemaCache($modelClass);

            $this->comment(sprintf('Meta Key "%s" is now deactivated for Model Class "%s". Happy developing!', $key, $modelClass));

            return self::SUCCESS;

        } catch (\RuntimeException $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
