# Dynamically configure and capture strictly-typed metadata for your Laravel models as fluent attributes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/smurfworks/laravel-model-meta.svg?style=flat-square)](https://packagist.org/packages/smurfworks/laravel-model-meta)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/smurfworks/laravel-model-meta/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/smurfworks/laravel-model-meta/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/smurfworks/laravel-model-meta/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/smurfworks/laravel-model-meta/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/smurfworks/laravel-model-meta.svg?style=flat-square)](https://packagist.org/packages/smurfworks/laravel-model-meta)

"Laravel Model Meta" allows you to capture strictly typed field values for a model without having to alter a database schema.

What separates this package from other meta packages available? A combination of the following:

- **Quick to implement with low domain impact:** This package will create only one meta table, regardless of the number of models you wish to store meta for and use a polymorphic relation.
- **Queryable data:** This package does not use JSON to serialize a model's meta, and instead the meta values are stored in separate database fields depending on the configured meta key's storage type, allowing for database querying using type-dependent operators and indexing.
- **Strict typing and content management:** The available meta keys are stored in their own database table, allowing for easy configuration via the command line, migration or your own custom GUI.
- **Reduced query load:** Available meta fields are cached using an index created from the available meta key table after changes - no unnecessary database queries and no schema column listing queries.
- **Compatible with your project flow:** Seeder helpers for importing meta keys from the standard Laravel seeding command, useful for project setup or keeping migrations ephemeral. Otherwise, register your model keys manually via the command line.
- **Along for the ride:** Transform meta values from one type to another, or move them to a new schema field once validated as required by your domain.
- **Database driver agnostic:** The package allows you to disable certain meta value types if your database driver doesn't support them, or you simply don't want them in the schema, so it won't create the columns when running migrations.

**To save you time, you should know that to use this package, the Primary keys of your models with metadata values must be incremental** - UUID relations are not currently supported. To solve this, you could alter the migrations and create your own class. 

## Installation

Please see [INSTALL](readme/INSTALL.md) for more information on how to install the package.

## Usage

Please see [USAGE](readme/USAGE.md) for more information on how to use the package.

## Configuration

Please see [CONFIGURATION](readme/CONFIGURATION.md) for more information on how to configure the package.

## Advanced details

For more information about some of the key design decisions and implementation details, please see [ADVANCED](readme/ADVANCED.md).

## Changelog

Please see [CHANGELOG](readme/CHANGELOG.md) for more information on what has changed recently.

## Contributing and Security Vulnerabilities

Please see [CONTRIBUTING](readme/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](readme/LICENSE.md) for more information.
