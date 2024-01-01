<?php

namespace SmurfWorks\ModelMeta\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use SmurfWorks\ModelMeta\Traits\HasModelMeta;

class DummyModelWithPrefix extends Model
{
    use HasModelMeta;

    protected $fillable = ['name'];

    public static function disableFillableKeys(): array
    {
        return ['registered_boolean'];
    }

    public static function getMetaKeyPrefix(): string
    {
        return 'custom_prefix';
    }
}
