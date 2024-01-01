<?php

namespace SmurfWorks\ModelMeta\Traits;

use Illuminate\Support\Facades\DB;
use SmurfWorks\ModelMeta\ModelMeta;

trait HasModelMeta
{
    /**
     * Changes to the meta records are stored here while pending a model save.
     */
    protected array $metaChanges = [];

    /**
     * To save meta values, we open a transaction and keep track of it, so we
     * may commit it when the model saves.
     */
    protected ?int $pendingTransaction = null;

    /**
     * In the case that the model wasn't created when we wanted to assign meta, the meta
     * changes array will still be holding the changes and this would be set to true, allowing
     * the applyMetaChanges method to handle the meta saving after the model is inserted.
     */
    protected bool $deferredMetaSaving = false;

    /**
     * Each model that has model meta will have many meta values assigned by the class and ID.
     */
    public function metaValues(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ModelMeta::getValueClass(), 'model');
    }

    /**
     * Bind some observable logic to ensure that meta saves when the model saves, and
     * only IF the model saves.
     */
    public static function bootHasModelMeta(): void
    {
        static::saving(fn ($model) => $model->prepareMetaChanges());
        static::saved(fn ($model) => $model->commitMetaChanges());
    }

    /**
     * Overriding this method to allow for meta values to be set via the fill method - there's an
     * option to disable specific keys on a model from being fillable by defining a 'disableFillableKeys'
     * method that returns each of the keys.
     *
     * {@inheritdoc}
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, array_keys(ModelMeta::getModelSchema(get_class($this))['keys']))
                && (! method_exists($this, 'disableFillableKeys')
                    || ! in_array($key, static::disableFillableKeys())
                )
            ) {
                $this->setMetaValue($key, $value);
                unset($attributes[$key]);
            }
        }

        return parent::fill($attributes);
    }

    /**
     * Overriding this method to allow for meta values to be retrieved. If a meta key is registered
     * for this model, then we prioritize loading from the metadata.
     *
     * {@inheritdoc}
     */
    public function getAttribute($key)
    {
        if (in_array($key, array_keys(ModelMeta::getModelSchema(get_class($this))['keys']))) {
            return $this->getMetaValue($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Overriding this method to allow for meta values to be set. Since so much of laravel models
     * are magic, we would need to know what properties exist on the model to be able to
     * distinguish meta values when acting fluently, instead we'll use a calculated schema
     * and prioritize values to be set as meta when registered, reducing schema lookups.
     *
     * {@inheritdoc}
     */
    public function setAttribute($key, $value): mixed
    {
        if (in_array($key, array_keys(ModelMeta::getModelSchema(get_class($this))['keys']))
            && ! in_array($key, ModelMeta::getModelSchema(get_class($this))['reserved'])
        ) {
            $this->setMetaValue($key, $value);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Apply the meta changes to the database, and clear the changes array.
     */
    protected function applyMetaChanges(): self
    {
        foreach ($this->metaChanges as $key => $value) {

            $fqKey = ModelMeta::getFQMetaKey(get_class($this), $key);

            /* Loads all meta relations if not already loaded to prevent n+1 queries, and then checks
             * for the one we need */
            $record = $this->metaValues->filter(fn ($v) => $v->key === $fqKey)->first();

            /* If this particular key doesn't exist for this model already, we'll set up a local object */
            if (! $record) {
                $record = $this->metaValues()->newModelInstance([
                    'model_type' => get_class($this),
                    'model_id' => $this->id,
                    'key' => $fqKey,
                ]);
            }

            $createdNow = ! $record->exists;

            /* Apply the value and save */
            $record->setValue($value);
            $record->save();

            /* Place the object inside the relation so the attribute getter returns an updated value */
            if ($createdNow) {
                $this->metaValues->push($record);
            } else {
                $this->metaValues->put($this->metaValues->search(fn ($v) => $v->id === $record->id), $record);
            }

            unset($this->metaChanges[$key]);
        }

        return $this;
    }

    /**
     * If a pending transaction is open, then we commit it. This is called after the model is saved,
     * ensuring the model is saved before the meta values are saved.
     */
    public function commitMetaChanges(): self
    {
        if (! is_null($this->pendingTransaction)) {

            if ($this->deferredMetaSaving) {
                $this->applyMetaChanges();
                $this->deferredMetaSaving = false;
            }

            DB::commit();
            $this->pendingTransaction = null;
        }

        return $this;
    }

    /**
     * Get the defined relations for this model - used to prevent reserved keys from being used.
     */
    public static function definedRelations(): array
    {
        $reflector = new \ReflectionClass(get_called_class());

        return collect($reflector->getMethods())
            ->filter(
                fn ($method) => ! empty($method->getReturnType()) &&
                    str_contains(
                        $method->getReturnType(),
                        'Illuminate\Database\Eloquent\Relations'
                    )
            )
            ->pluck('name')
            ->all();
    }

    /**
     * Get the meta value for the given key, handling the eager load if required.
     * Remember to eager load your meta value if you're calling multiple records
     * as lazy loads will cause an n+1 query.
     */
    public function getMetaValue(string $key): mixed
    {
        return $this->metaValues->filter(
            fn ($v) => $v->key === ModelMeta::getFQMetaKey(get_class($this), $key)
        )->first()?->value ?? ModelMeta::getModelSchema(get_class($this))['keys'][$key]['default_value'];
    }

    /**
     * If there are pending meta changes, then we start a transaction and make the queries
     * required to persist them. This is called before the model is saved (saving event),
     * and the transaction for the meta values is committed after the model is saved.
     */
    public function prepareMetaChanges(): self
    {
        if (count($this->metaChanges) === 0) {
            return $this;
        }

        /* Put the meta changes into a transaction that we may commit */
        DB::beginTransaction();
        $this->pendingTransaction = DB::transactionLevel();

        if (! $this->exists) {
            $this->deferredMetaSaving = true;

            return $this;
        }

        $this->applyMetaChanges();

        return $this;
    }

    /**
     * Join the meta table where the key is as requested.
     */
    public function scopeWithMeta(\Illuminate\Database\Eloquent\Builder $query, string $key): \Illuminate\Database\Eloquent\Builder
    {
        $tempTableName = sprintf('values_%s', $key);
        $schema = ModelMeta::getModelSchema(get_class($this))['keys'][$key];

        return $query
            ->addSelect(sprintf('%s.*', $this->getTable()))
            ->leftJoin(
                sprintf(
                    '%s as %s',
                    config('model-meta.tables.values', 'model_meta_values'),
                    $tempTableName
                ),
                function ($join) use ($key, $tempTableName) {
                    $join->on(sprintf('%s.model_id', $tempTableName), '=', sprintf('%s.id', $this->getTable()))
                        ->where(sprintf('%s.model_type', $tempTableName), get_class($this))
                        ->where(sprintf('%s.key', $tempTableName), ModelMeta::getFQMetaKey(get_class($this), $key));
                }
            )->addSelect(sprintf('%s.%s as %s', $tempTableName, $schema['store_value_as'], $key));
    }

    /**
     * Apply a change to the meta records of this model. Saving will occur at the same time
     * as the model itself saving.
     */
    public function setMetaValue(string $key, mixed $value): self
    {
        $this->metaChanges[$key] = $value;

        return $this;
    }
}
