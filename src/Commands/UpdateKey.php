<?php

namespace SmurfWorks\ModelMeta\Commands;

use SmurfWorks\ModelMeta\Abstracts\KeyCommand;

class UpdateKey extends KeyCommand
{
    public $signature = 'model-meta:update-key';

    public $description = 'Update an existing meta key for a model';

    public function handle(): int
    {
        try {
            $this->alert('Existing values types are not converted automatically when updating a keys value type - you must write and execute a manual migration of that data.');

            $this->clearModelCache();
            $modelClass = $this->selectModel('What model contains the key that you would like to update?');
            $key = $this->selectKey($modelClass, 'Which key would you like to update?');

            /* The above selectKey will validate this exists */
            $record = $this->findKey($modelClass, $key);

            /* Append field changes to this array */
            $update = [];

            if ($this->confirm('Would you like to change the value type?')) {
                $update['store_value_as'] = $this->selectValueType('Please select the new value type');
            }

            if ($this->confirm('Would you like to update the default value?')) {
                $update['default_value'] = $this->ask('Please enter your new default value');
            }

            if ($this->confirm('Would you like to update the description?')) {
                $update['description'] = $this->ask('Please enter your new description');
            }

            /* Apply updates to the key */
            if (! count($update)) {
                throw new \RuntimeException('No updates have been selected, please try again.');
            }

            $record->update($update);
            $this->clearSchemaCache($modelClass);

            $this->comment(sprintf('Meta Key "%s" has been updated for Model Class "%s". Remember to migrate existing values when the value type is changed. Happy developing!', $key, $modelClass));

            return self::SUCCESS;

        } catch (\RuntimeException $error) {

            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
