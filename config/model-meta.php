<?php

use SmurfWorks\ModelMeta\ModelMeta;

return [

    /* Customise table names as you see fit. */
    'tables' => [
        //'keys' => 'model_meta_keys',
        //'values' => 'model_meta_values',
    ],

    /* Customise the classes used for the meta tables */
    'classes' => [
        //'keys' => \SmurfWorks\ModelMeta\Models\ModelMetaKey::class,
        //'values' => \SmurfWorks\ModelMeta\Models\ModelMetaValue::class,
    ],

    /* Specify the namespace to find the models that we can register meta for (They must also use HasModelMeta) */
    //'model-namespace' => ['App\Models'],

    /* Customise the available types of stored values by disabling value types you don't need */
    'disable-value-types' => [
        //ModelMeta::TYPE_STRING,
        //ModelMeta::TYPE_TEXT,
        ModelMeta::TYPE_FULLTEXT,
        //ModelMeta::TYPE_INTEGER,
        //ModelMeta::TYPE_FLOAT,
        //ModelMeta::TYPE_BOOLEAN,
        //ModelMeta::TYPE_JSON,
        ModelMeta::TYPE_JSONB,
        //ModelMeta::TYPE_DATE,
        //ModelMeta::TYPE_TIME,
        //ModelMeta::TYPE_DATETIME,
        //ModelMeta::TYPE_TIMESTAMP,
    ],
];
