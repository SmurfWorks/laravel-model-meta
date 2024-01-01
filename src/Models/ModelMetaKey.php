<?php

namespace SmurfWorks\ModelMeta\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use SmurfWorks\ModelMeta\ModelMeta;

/**
 * @property string $key
 * @property string $description
 * @property string $model_type
 * @property string $store_value_as
 * @property string $default_value
 * @property string $original_key
 * @property array $schema
 */
class ModelMetaKey extends \Illuminate\Database\Eloquent\Model
{
    use SoftDeletes;

    protected $fillable = [

        'key', 'description', 'model_type', 'store_value_as', 'default_value',
    ];

    protected $casts = [

        'key' => 'string',
        'description' => 'string',
        'model_type' => 'string',
        'store_value_as' => 'string',
        'default_value' => 'string',
    ];

    /**
     * Each meta key has many meta values to load.
     */
    public function metaValues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ModelMeta::getValueClass(), 'key', 'key');
    }

    /**
     * Override to allow config-defined table names to take effect using this model.
     *
     * {@inheritdoc}
     */
    public function getTable()
    {
        return config('model-meta.tables.keys', 'model_meta_keys');
    }

    /**
     * Add serialization as part of interacting with this attribute.
     */
    public function defaultValue(): Attribute
    {
        return Attribute::make(
            fn ($value) => ! is_null($value) ? unserialize($value) : null,
            function ($value) {

                if ($value === '' || $value === null) {
                    return null;
                }

                switch ($this->attributes['store_value_as']) {
                    case ModelMeta::TYPE_STRING:
                    case ModelMeta::TYPE_TEXT:
                    case ModelMeta::TYPE_FULLTEXT:
                        $value = (string) $value;
                        break;

                    case ModelMeta::TYPE_INTEGER:
                        $value = (int) $value;
                        break;

                    case ModelMeta::TYPE_FLOAT:
                        $value = (float) $value;
                        break;

                    case ModelMeta::TYPE_BOOLEAN:
                        $value = ! in_array($value, [false, 0, '0', 'false', 'no'], true);
                        break;

                    case ModelMeta::TYPE_DATE:
                        $value = $this->asDate($value);
                        break;

                    case ModelMeta::TYPE_TIME:
                        $value = (string) $value;
                        break;

                    case ModelMeta::TYPE_DATETIME:
                        $value = $this->asDateTime($value);
                        break;

                    case ModelMeta::TYPE_TIMESTAMP:
                        $value = $this->asTimestamp($value);
                        break;
                }

                return ! is_null($value) ? serialize($value) : null;
            }
        );
    }

    /**
     * Get the original key used to create this key record.
     */
    public function originalKey(): Attribute
    {
        return Attribute::make(fn () => explode(':', $this->key)[1]);
    }

    /**
     * Return the contents of the cached schema for this key record.
     */
    public function schema(): Attribute
    {
        return Attribute::make(
            fn () => [
                'fq_key' => $this->key,
                'original_key' => $this->original_key,
                'store_value_as' => $this->store_value_as,
                'default_value' => $this->default_value,
            ]
        );
    }
}
