# Using Model Meta

## Registering keys

Register as many new meta keys as you need for each of your models:

```bash
php artisan model-meta:create-key
```

When configuring a key, by default the configuration is stored in the `model_meta_keys` table. The `key` column will
store the key you choose, but it will store it with a prefix. By default, the prefix is the table name of the model
but may be customised (see [CONFIGURATION](./CONFIGURATION.md)). The prefix is so that we may have a foreign key constraint
with cascading deletes between keys and stored values. The prefix and key are separated by a colon, which as a result 
means **we may not use colons in our key names**.

Additionally, the `key` column must not conflict with the schema attributes or relations of the model - there is
validation to prevent this.

A description is also collected as it's always good to document what a key is for.

### Default values

A default value may be set for each key, which will be used when retrieving the attribute if no value is set. This will
not be returned in direct database queries, or direct access through the relation, as the default is app-layer logic
beyond the database or ORM. You can assume `null` to be whatever default value you choose in those scenarios.

## Fluent Attribute Access

Assuming you set up a meta key with the name `my_meta_key` for your `User` model with a value type of `string`, you can now use the meta key fluently like you would any other model attribute:

```php
$user = User::first();
$user->my_meta_key = 'my meta value';
$user->save();
var_dump($user->my_meta_key); // string(13) "my meta value"

/* Refresh the record */
$user = User::first();
var_dump($user->my_meta_key); // string(13) "my meta value"

/* Fill */
$user->fill(['my_meta_key' => 'a second value'])->save();
var_dump($user->my_meta_key); // string(14) "a second value"

/* Update */
$user->update(['my_meta_key' => 'a third value']);
var_dump($user->my_meta_key); // string(13) "a third value"

/* Create */
User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'my_meta_key' => 'Some value'
]);

```

## Direct access

If model magic is getting in your way, you may use the `metaValues` relation to get that data you need.

```php
$user->metaValues()->firstWhere('key', 'users:my_meta_key')->value; // string(13) "a third value"
```

In a completely unvalidated use case, you may want to access the key record and/or original key from the value:

```php
$user->metaValues->first()->metaKey->original_key; // string(11) "my_meta_key"
```

**Note:** Through direct access, the value record may not exist, and null would be returned instead of your default.

## Other commands available for managing keys:

```bash
Available commands for the "model-meta" namespace:

  model-meta:create-key      Create a new meta key for a model
  model-meta:deactivate-key  Deactivate an existing meta key for a model without removing existing values
  model-meta:delete-key      Permanently delete an existing meta key for a model and all associated values
  model-meta:list-keys       List the keys available for a specific model
  model-meta:reactivate-key  Reactivate an existing meta key for a model
  model-meta:update-key      Update an existing meta key for a model

```

## Events

As this package uses its own models for keys and values, you can hook into the events of those models.

```php
\SmurfWorks\ModelMeta\Models\ModelMetaValue::saved(fn ($v) => logger()->info(sprintf('Saved Meta: %s', $v->value)));
```

## Seeding meta keys

If you're working with a repository, you'll likely want to create a seeder for managing the available attributes for
syncing across environments or automated deployment. This might be the kind of thing you use on first deployment to an
environment, or it might be a seeder you call on every deployment - the choice is yours really. Simply apply the trait
to a seeder, register your calls, and then you're good to go.

The calls will update existing keys if they exist already, or create the meta key if not.

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use SmurfWorks\ModelMeta\ModelMeta;
use SmurfWorks\ModelMeta\Traits\ModelMetaSeeder;

class ModelMetaSeeder extends Seeder
{
    use ModelMetaSeeder;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createMetaKey(
            modelClass: User::class,
            key: 'high_score',
            type: ModelMeta::TYPE_INTEGER,
            description: 'The user\'s high score',
            default: null
        );
        
        /* Short form syntax */
        $this->createMetaKey(User::class, 'first_score', ModelMeta::TYPE_INTEGER)
            ->createMetaKey(User::class, 'last_score', ModelMeta::TYPE_INTEGER)
            ->createMetaKey(User::class, 'last_played', ModelMeta::TYPE_DATETIME)
            ->createMetaKey(User::class, 'preferred_mode', ModelMeta::TYPE_STRING, default: 'ranked');
    
        // ...
    }
}

```

and then execute whenever you want with:

```bash
php aritsan db:seed --class=ModelMetaSeeder --force
```

## Migrating data to a real schema

The use cases for a package with dynamic schema field definitions like this are typically ambiguous to say the least,
whether it be unclear client requirements, experimental tagging or just ephemeral information about your models that
may be no longer required at any time.

One thing that is for sure in these use-cases is that there's a chance you may want to convert the meta values to
real database schema column values. This is typically a trivial thing to do, but in trying to take responsibility for
the technical debt created by this package, a helper has been added to make converting as painless as possible.

**When using the helper, it will return the default value if no value is defined for the model.**

### 1. Define and run a migration
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('high_score')->nullable();
        });
    }

    // ...
}
```

```bash
php artisan migrate
```

**Note:** Remember to add the new attribute to `$fillable` on your model, and remove this meta key from your seeder
if you're using one.

### 2. Create and run a command to move the data

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SmurfWorks\ModelMeta\ModelMeta;

class MigrateHighScores extends Command
{
    public $signature = 'app:migrate-high-scores';

    public $description = 'Migrate user high scores from meta to a schema column';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ModelMeta::migrateValues(
            \App\Models\User::class,
            'high_score',
            fn ($model, $value) => $model->update(['high_score' => $value])
        );
    }
}

```

### 3. Remove the meta key
```bash
php artisan model-meta:delete-key
```

**Note: ** This command will delete each of the database values before deleting the key, so don't forget to validate
the data has moved to the new field correctly before proceeding.

Next: [Read about configuration](./CONFIGURATION.md)
