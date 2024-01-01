<?php

namespace SmurfWorks\ModelMeta\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use SmurfWorks\ModelMeta\ModelMeta;

/**
 * @property string $model_type
 * @property int $model_id
 * @property string $key
 * @property string $value_stored_as
 * @property string $string
 * @property string $text
 * @property string $fulltext
 * @property int $integer
 * @property float $float
 * @property bool $boolean
 * @property object $json
 * @property object $jsonb
 * @property string $date
 * @property string $time
 * @property \Carbon\Carbon $datetime
 * @property \Carbon\Carbon $timestamp
 * @property mixed $value
 */
class ModelMetaValue extends \Illuminate\Database\Eloquent\Model
{
    use SoftDeletes;

    protected $fillable = [

        'model_type', 'model_id', 'key', 'value_stored_as',

        'string', 'text', 'fulltext', 'integer', 'float', 'boolean',
        'json', 'jsonb', 'date', 'time', 'datetime', 'timestamp',
    ];

    protected $casts = [

        'string' => 'string',
        'text' => 'string',
        'fulltext' => 'string',
        'integer' => 'integer',
        'float' => 'float',
        'boolean' => 'boolean',
        'json' => 'object',
        'jsonb' => 'object',
        'date' => 'date',
        'time' => 'string',
        'datetime' => 'datetime',
        'timestamp' => 'timestamp',
    ];

    protected $visible = [
        'key', 'value_stored_as', 'value',
    ];

    protected $hidden = [
        'id', 'model_type', 'model_id', 'created_at', 'updated_at', 'deleted_at',
        'string', 'text', 'fulltext', 'integer', 'float', 'boolean',
        'json', 'jsonb', 'date', 'time', 'datetime', 'timestamp',
    ];

    protected $appends = ['value'];

    /**
     * Each meta value belongs to a particular key record.
     */
    public function metaKey(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelMeta::getKeyClass(), 'key', 'key');
    }

    /**
     * Get the model that this value is stored for.
     */
    public function model(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Override to allow config-defined table names to take effect using this model.
     *
     * {@inheritdoc}
     */
    public function getTable()
    {
        return config('model-meta.tables.values', 'model_meta_values');
    }

    /**
     * Get the value based on what the value was stored as, using Laravel casting on the attribute.
     */
    public function value(): Attribute
    {
        return Attribute::make(fn () => $this->getAttribute($this->value_stored_as));
    }

    /**
     * Set the value on this field, making sure all other columns are nulled, and we're tracking
     * which column the latest value is stored in.
     */
    public function setValue(mixed $value): void
    {
        $storeAs = ModelMeta::getModelSchema($this->model_type)['keys'][ModelMeta::getOriginalMetaKey($this->key)]['store_value_as'];

        $this->setAttribute('value_stored_as', $storeAs);

        /* Clean up the other fields after type changed */
        foreach (ModelMeta::storedValueTypes() as $field) {
            $this->setAttribute($field, null);
        }

        $this->setAttribute($storeAs, $value);
    }
}
