# Customising your installation of the Model Meta

In an effort to be as extensible as possible, the package allows you to branch most customisation through the config
file to your own files.

Remember to publish that configuration file first:

```bash
php artisan vendor:publish --tag="laravel-model-meta-config"
```

## You model namespace

Since we're extending models, it's helpful if we're able to aggregate a list of models that are currently using the
`HasModelMeta` trait. By default, the package will look for models in the `App\Models` namespace. If you're using a
different namespace, you can specify it as below:

```php
<?php

// config/model-meta.php

use SmurfWorks\ModelMeta\ModelMeta;

return [

    // ...

    /* Specify the namespace to find the models that we can register meta for (They must also use HasModelMeta) */
    'model-namespace' => ['App\Models', 'CustomApp\Models', 'Package\Models'],
    
    // ...
];

```

## Custom table names

You can customise the table names used by the package by publishing the config file and changing the values. These table
names will be used in the published migration, so you will not need to update the migrations.

```php
<?php

// config/model-meta.php

use SmurfWorks\ModelMeta\ModelMeta;

return [

    /* Customise table names as you see fit. */
    'tables' => [
        'keys' => 'custom_keys_table',
        'values' => 'custom_values_table',
    ],
    
    // ...
];

```

## Custom classes

In the case that you want to extend the package default classes, you may write your classes to extend the classes
and register them in the configuration. If you do not extend the classes, do remember that you'll need to map your
table names and migrations manually. Extending the package class lets you utilize the table mapping configuration
above automatically.

### 1. Create custom class:

```php
<?php

// config/model-meta.php

namespace SmurfWorks\ModelMeta\Tests\Models;

use SmurfWorks\ModelMeta\Models\ModelMetaKey;

class CustomKey extends ModelMetaKey
{
    // Add custom functionality here
}

```

### 2. Register in Configuration:

```php
<?php

// config/model-meta.php

use SmurfWorks\ModelMeta\ModelMeta;

return [

    // ...

    /* Customise the classes used for the meta tables */
    'classes' => [
        'keys' => \SmurfWorks\ModelMeta\Tests\Models\CustomKey::class,
        
        // ...
    ],
    
    // ...
];

```

## Disabling available data types

In some cases you may need to disable column types. For example SQLite can't work with fulltext or jsonb columns very
well, so they're disabled by default. Simply add any constants to the array entry below that you would like disabled. 

If you configure this before running your migration, the disabled columns will not be created and will not be available
when creating new meta keys.

If your migration has already run, and you would like to disable a column type, simply add it to the array using it's
constant and it won't be available for new meta keys. However, the column will remain in your values table.

If you'd like to remove an item from the array to enable it and migrations have already run, you will need to add the
column via a migration manually.

```php
<?php

// config/model-meta.php

use SmurfWorks\ModelMeta\ModelMeta;

return [

    // ...

    /* Customise the available types of stored values by disabling value types you don't need */
    'disable-value-types' => [
        //ModelMeta::TYPE_STRING,
        //ModelMeta::TYPE_TEXT,
        ModelMeta::TYPE_FULLTEXT,
        ModelMeta::TYPE_INTEGER, // Disabling integers
        //ModelMeta::TYPE_FLOAT,
        //ModelMeta::TYPE_BOOLEAN,
        //ModelMeta::TYPE_JSON,
        ModelMeta::TYPE_JSONB,
        //ModelMeta::TYPE_DATE,
        //ModelMeta::TYPE_TIME,
        //ModelMeta::TYPE_DATETIME,
        //ModelMeta::TYPE_TIMESTAMP,
    ]
];

```

## Customising prefixes

Prefixes are used by the key and value tables to create a uniquely dependent foreign key relation for cascading value
deletion when deleting a key, as well as when formulating the cache key for a model's available keys.

By default, the prefix is the table name of the model, but you may customise it to be anything you like at the model
level:

```php
<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasModelMeta;

    // ...
    
    public static function getMetaKeyPrefix(): string
    {
        return 'custom_prefix';
    }
           
    // ...
}

```

## Disabling fillable functionality

By default, all meta keys can be filled on a model, as if they existed in the `$fillable` array. In some cases this may
create a security vunerability if not properly managed. You may disable keys individually by returning them from a
method on the model:

```php
<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasModelMeta;

    // ...
    
    public static function disableFillableKeys(): array
    {
        return ['my_disabled_key'];
    }
           
    // ...
}

```

Next: [Read about advanced](./ADVANCED.md)
