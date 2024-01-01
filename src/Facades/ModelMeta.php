<?php

namespace SmurfWorks\ModelMeta\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SmurfWorks\ModelMeta\ModelMeta
 *
 * @codeCoverageIgnore
 */
class ModelMeta extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SmurfWorks\ModelMeta\ModelMeta::class;
    }
}
