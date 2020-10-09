# Bagisto
[![Latest Version on Packagist](https://img.shields.io/packagist/v/bagisto-europe/laravel-data-faker.svg?style=flat-square)](https://packagist.org/packages/bagisto-europe/laravel-data-faker)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/bagisto-europe/laravel-data-faker/run-tests?label=tests)](https://github.com/bagisto-europe/laravel-data-faker/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/bagisto-europe/laravel-data-faker.svg?style=flat-square)](https://packagist.org/packages/bagisto-europe/laravel-data-faker)

### 1. Introduction:

Data Faker module provides fake Data for bagisto.  
This allows us to generate fake data that can be populated into the database during development for testing purposes.

By using this extension, you can generate fake data for the bagisto product and customer and categories.

It packs some feature:

* The user can create Product fake data.
* The user can create Users fake data.
* The user can create some Category fake data.
* The user can create Product Category fake data.

### 2. Requirements:

* **Bagisto**: v1.2.0

### 3. Installation:

```sh
composer require bagisto/laravel-datafaker
```

* Run the following command below to complete the setup

```sh
php artisan db:seed --class="Webkul\DataFaker\Database\Seeders\DatabaseSeeder"
```