<?php

namespace SmurfWorks\ModelMeta\Abstracts;

use Illuminate\Console\Command;
use SmurfWorks\ModelMeta\ModelMeta;
use SmurfWorks\ModelMeta\Models\ModelMetaKey;

abstract class KeyCommand extends Command
{
    /**
     * Clear the model cache to make sure we're working with an up-to-date list.
     */
    protected function clearModelCache(): void
    {
        ModelMeta::clearModelCache();
    }

    /**
     * Clear the schema cache for the given model class after making changes to it's utilized keys.
     */
    protected function clearSchemaCache(string $modelClass): void
    {
        ModelMeta::clearSchemaCache($modelClass);
    }

    /**
     * Find a key by its original key name for the given modelClass - adapt the query if you need to work with a
     * particular criteria of key.
     */
    protected function findKey(string $modelClass, string $key, ?\Closure $modifyQuery = null): ?ModelMetaKey
    {
        $query = ModelMeta::getKeyClass()::query()
            ->where('key', ModelMeta::getFQMetaKey($modelClass, $key))
            ->where('model_type', $modelClass);

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        return $query->first();
    }

    /**
     * Get a list of keys for the given model class - adapting the query criteria if required.
     */
    protected function getKeyList(string $modelClass, ?\Closure $modifyQuery = null): array
    {
        $query = ModelMeta::getKeyClass()::query()
            ->where('model_type', $modelClass);

        if ($modifyQuery) {
            $modifyQuery($query);
        }

        $results = $query->get();
        if (! $results->count()) {
            throw new \RuntimeException('No keys are currently available to this action.');
        }

        return array_values($results->map(fn ($v) => $v->original_key)->all());
    }

    /**
     * Get a list of models that are configured with HasModelMeta in the project's model namespace.
     */
    protected function getModelList(): array
    {
        $modelList = ModelMeta::configuredModels();
        if (! count($modelList)) {
            throw new \RuntimeException('No models are configured with HasModelMeta in your model namespace, please configure at least one model first.');
        }

        return $modelList;
    }

    /**
     * Select a model from the list of configured models.
     */
    protected function selectModel(string $question): string
    {
        return $this->choice($question, $this->getModelList());
    }

    /**
     * Select a key that belongs to the given model class - adapt the query if you need to work with a particular
     * criteria of key.
     */
    protected function selectKey(string $modelClass, string $question, ?\Closure $modifyQuery = null): string
    {
        return $this->choice($question, $this->getKeyList($modelClass, $modifyQuery));
    }

    /**
     * Select a value type from the list of available value types.
     */
    protected function selectValueType(string $question): string
    {
        return $this->choice($question, ModelMeta::storedValueTypes());
    }
}
