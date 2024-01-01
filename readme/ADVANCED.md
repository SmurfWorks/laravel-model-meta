# Advanced details about concepts and features

## About the caching

Given we're working with what are effectively content-manageable attributes for models, a lot of the logic required to
get the data stored and retrieved needs to know what attributes are meta keys and what ones are default attributes.

To avoid having to query the database for this information, we cache the available keys for each model using
the model prefix, which can be customised per model (See [CONFIGURATION](./CONFIGURATION.md)).

The cache management is automatic when interfacing with the commands above, but if you're managing keys manually, it's
best to use the `ModelMeta::clearSchemaCache(string $modelClass): void` method to clear the cache for a model.

Additionally, to avoid unnecessary filesystem calls when listing models, whenever you add the `HasModelMeta` trait to a
model, the cache for the list of applicable models in your namespace will need to be cleared:
`ModelMeta::clearModelCache(): void`

## Reducing queries

In addition to caching the schema, to reduce the number of queries performed when working with meta values, we do a little bit of work after saving a meta
value to place it back into the relation collection, rather than having to query the database again, which then allows
the normal attribute get magic to take effect.

Additionally, the model saving/saved events are used to save the meta values in a transaction and only commit the
transaction if the model save is successful. The effect of this is that the model does not save unless a non-meta
attribute is saving, and meta is only saved if a meta attribute is saving.

## Why not serialization? Query operators!

The main reason is that we want to be able to query the values stored in the database using the database's native
operators. If we were to serialize the values, we would need to either use JSON operators for all queries (which can be
slow on bulk read/write functionality) or pull all the values out of the database and then filter or operate as
required. Instead, now we can use joins, where clauses and other native database functionality to get the data we need.

```php

// An arbitrary join query

User::query()->join(
        function ($join) {
            $join->on('users.id', '=', 'model_meta_values.model_id')
                ->where('model_meta_values.model_type', '=', User::class)
                ->where('model_meta_values.key', '=', 'users:high_score');
        }
    )
    ->where('model_meta_values.integer', '>', 100000)
    ->get();

// An eloquent scope join
User::withMeta('high_score')->where('high_score', '>', 100000)->get();
User::withMeta('bio')->whereRaw('MATCH (bio) AGAINST (\'painting\' IN BOOLEAN MODE)')->get();

```

**NOTE:** `withMeta(string $key)` will only return the value of the key's current value type. If the value data type
has changed, and values haven't been migrated, then a null value will be returned.

## Default values

The goal with this feature, without creating another table, was to introduce value defaults in a way that allowed
minimal impact on the initial package design but still be where we need them to be. The solution was to introduce a new
field to return when no value was available. This does however mean that ORM and Database queries do miss out on this
functionality. You can assume that any null values in these situations are your default value.

In the instance you're doing a count of models with X value, make sure you combine the null sum with the default value
sum for your totals. 

#### Further reading:

- [Contributing](./CONTRIBUTING.md)
- [Licensing information](./LICENSE.md)
