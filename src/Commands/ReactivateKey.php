<?php

namespace SmurfWorks\ModelMeta\Commands;

use SmurfWorks\ModelMeta\Abstracts\KeyCommand;

class ReactivateKey extends KeyCommand
{
    public $signature = 'model-meta:reactivate-key';

    public $description = 'Reactivate an existing meta key for a model';

    public function handle(): int
    {
        try {
            $this->clearModelCache();
            $modelClass = $this->selectModel('What model contains the key you want to reactivate?');
            $key = $this->selectKey($modelClass, 'What key would you like to reactivate?', fn ($q) => $q->withTrashed()->whereNotNull('deleted_at'));

            /* selectKey will validate this exists */
            $record = $this->findKey($modelClass, $key, fn ($q) => $q->withTrashed()->whereNotNull('deleted_at'));

            if (! $this->confirm('The key is ready to reactivate, continue?')) {
                throw new \RuntimeException('Reactivation has been cancelled.');
            }

            $record->restore();
            $this->clearSchemaCache($modelClass);

            $this->comment(sprintf('Meta Key "%s" is now reactivated for Model Class "%s". Happy developing!', $key, $modelClass));

            return self::SUCCESS;

        } catch (\RuntimeException $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
