<?php

namespace SmurfWorks\ModelMeta\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use SmurfWorks\ModelMeta\Traits\HasModelMeta;

class DummyModel extends Model
{
    use HasModelMeta;

    protected $fillable = ['name'];
}
