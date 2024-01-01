<?php

namespace SmurfWorks\ModelMeta\Commands;

use SmurfWorks\ModelMeta\Abstracts\KeyCommand;

class DeleteKey extends KeyCommand
{
    public $signature = 'model-meta:delete-key';

    public $description = 'Permanently delete an existing meta key for a model and all associated values';

    public function handle(): int
    {
        try {

            $this->clearModelCache();
            $modelClass = $this->selectModel('What model contains the key you want to delete?');
            $key = $this->selectKey($modelClass, 'What key would you like to delete?', fn ($q) => $q->withTrashed());

            /* selectKey will validate this exists */
            $record = $this->findKey($modelClass, $key, fn ($q) => $q->withTrashed());

            /* Confirm we're proceeding, and ask for the value type */
            if ($this->ask('Type the name of the key to confirm deletion - incorrect values will cancel the action') !== $key) {
                throw new \RuntimeException('Deletion has been cancelled.');
            }

            /* While we have the database cascade in place, this is assertive for deleting values if foreign keys
             * aren't enabled, which is the case in testing environments with SQLite */
            $record->metaValues()->forceDelete();

            /* Hard-delete the meta key and cascade the value records */
            $record->forceDelete();
            $this->clearSchemaCache($modelClass);

            $this->comment(sprintf('Meta Key "%s" is now deleted for Model Class "%s" with all values removed. New entries will be ignored. Happy developing!', $key, $modelClass));

            return self::SUCCESS;

        } catch (\RuntimeException $error) {

            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
