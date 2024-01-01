# Installing the package

You can install the package via composer:

```bash
composer require smurfworks/laravel-model-meta
```

You need to publish the migrations and configuration with:

```bash
php artisan vendor:publish --provider="SmurfWorks\ModelMeta\ModelMetaServiceProvider"
```

It's recommended you review the [configuration](./CONFIGURATION.md) and change any defaults. You may publish the config file with:

After confirming the configuration, you can run the migrations:

```bash
php artisan migrate
```

Add the `HasModelMeta` trait to your desired models:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Smurfworks\ModelMeta\HasModelMeta;

class User extends Authenticatable
{
    use HasModelMeta;
    
    ...
} 
```

Your models can now register meta keys and access them fluently as dynamic attributes.

Next: [Read about usage](./USAGE.md)
