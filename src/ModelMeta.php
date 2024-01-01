<?php

namespace SmurfWorks\ModelMeta;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelMeta
{
    const TYPE_STRING = 'string';

    const TYPE_TEXT = 'text';

    const TYPE_FULLTEXT = 'fulltext';

    const TYPE_INTEGER = 'integer';

    const TYPE_FLOAT = 'float';

    const TYPE_BOOLEAN = 'boolean';

    const TYPE_JSON = 'json';

    const TYPE_JSONB = 'jsonb';

    const TYPE_DATE = 'date';

    const TYPE_TIME = 'time';

    const TYPE_DATETIME = 'datetime';

    const TYPE_TIMESTAMP = 'timestamp';

    /**
     * Clear the model cache to make sure we're working with an up-to-date list.
     */
    public static function clearModelCache(): void
    {
        cache()->forget('model-meta:configured-models');
    }

    /**
     * Clear the schema cache for the given model class after making changes to it's utilized keys.
     */
    public static function clearSchemaCache(string $modelClass): void
    {
        cache()->forget(sprintf('model-meta:schema:%s', ModelMeta::getModelPrefix($modelClass)));
    }

    /**
     * Return the model classes available to assign meta for.
     */
    public static function configuredModels(): array
    {
        $namespaces = config('model-meta.model-namespace', ['App\Models']);

        return cache()->rememberForever(
            'model-meta:configured-models',
            function () use ($namespaces) {
                $classes = [];

                foreach ($namespaces as $namespace) {
                    $classes = array_merge(
                        $classes,
                        array_values(
                            array_filter(
                                ClassFinder::getClassesInNamespace($namespace),
                                fn ($c) => is_subclass_of($c, Model::class)
                                    && in_array(Traits\HasModelMeta::class, class_uses($c))
                            )
                        )
                    );
                }

                return array_unique($classes);
            }
        );
    }

    /**
     * Meta key strings are stored with a prefix for unique identification per model without having
     * to use the model_type field as a qualifier.
     */
    public static function getFQMetaKey(string $modelClass, string $key): string
    {
        $prefix = static::getModelPrefix($modelClass);

        return sprintf('%s:%s', $prefix, $key);
    }

    /**
     * Return the class name of the model that's used to represent meta keys.
     */
    public static function getKeyClass(): string
    {
        return config('model-meta.classes.keys', Models\ModelMetaKey::class);
    }

    /**
     * Get the meta prefix for the given model class.
     */
    public static function getModelPrefix(string $modelClass): string
    {
        return (method_exists($modelClass, 'getMetaKeyPrefix'))
            ? $modelClass::getMetaKeyPrefix()
            : (new $modelClass)->getTable();
    }

    /**
     * Get the meta schema for a given model class. Caching included to reduce database queries.
     */
    public static function getModelSchema(string $modelClass): array
    {
        return cache()->rememberForever(
            sprintf('model-meta:schema:%s', static::getModelPrefix($modelClass)),
            fn () => [

                'keys' => static::getKeyClass()::where('model_type', $modelClass)->get()->mapWithKeys(
                    fn ($keyRecord) => [$keyRecord->original_key => $keyRecord->schema]
                )->all(),

                'reserved' => array_unique(
                    array_map(
                        fn ($v) => strtolower((string) str($v)->snake()),
                        array_merge(
                            array_map(
                                fn ($v) => $v['name'],
                                Schema::getColumns((new $modelClass)->getTable())
                            ),
                            (new $modelClass)->definedRelations(),
                        )
                    )
                ),
            ]
        );
    }

    /**
     * Find the original key without the automatically prepended prefix.
     */
    public static function getOriginalMetaKey(string $fqKey): string
    {
        return explode(':', $fqKey)[1];
    }

    /**
     * Return the class name of the model that's used to represent meta values.
     */
    public static function getValueClass(): string
    {
        return config('model-meta.classes.values', Models\ModelMetaValue::class);
    }

    /**
     * A helper to process values from your database safely. Typically, this would be used to move meta values to the
     * database before deleting the key.
     *
     * @param  int  $chunkSize
     */
    public static function migrateValues(
        string $modelClass,
        string $key,
        \Closure $process,
        $chunkSize = 50
    ): void {
        /**
         * Refresh the reserved keys after a field is added to the schema
         *
         * @see Traits\HasModelMeta::setAttribute()
         */
        static::clearSchemaCache($modelClass);

        static::getValueClass()::where('model_type', $modelClass)
            ->where('key', static::getFQMetaKey($modelClass, $key))
            ->with('model')
            ->chunk(
                $chunkSize,
                fn ($chunked) => $chunked->each(fn ($single) => $process($single->model, $single->value))
            );
    }

    /**
     * Return a configuration array for the available stored value types. The array can be altered
     * by available configuration values.
     */
    public static function storedValueTypes(): array
    {
        $configuration = [
            self::TYPE_STRING,
            self::TYPE_TEXT,
            self::TYPE_FULLTEXT,
            self::TYPE_INTEGER,
            self::TYPE_FLOAT,
            self::TYPE_BOOLEAN,
            self::TYPE_JSON,
            self::TYPE_JSONB,
            self::TYPE_DATE,
            self::TYPE_TIME,
            self::TYPE_DATETIME,
            self::TYPE_TIMESTAMP,
        ];

        return array_filter($configuration, fn ($v) => ! in_array($v, config('model-meta.disable-value-types', [])));
    }
}
