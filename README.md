# Bagisto
[![Total Downloads](https://img.shields.io/packagist/dt/bagisto/laravel-datafaker.svg?style=flat-square)](https://packagist.org/packages/bagisto/laravel-datafaker)

### 1. Introduction:

This module allows you to generate fake data that can be populated into the database during development for testing purposes.

* The user can create Product fake data.
* The user can create Users fake data.
* The user can create some Category fake data.
* The user can create Product Category fake data.

### 2. Requirements:

* **Bagisto**: v1.2.0

### 3. Installation:

```sh
composer require robinflyhigh/bagisto-laravel-data-faker
```

* Run the following command below to complete the setup

```sh
php artisan db:seed --class="Webkul\DataFaker\Database\Seeders\DatabaseSeeder"
```
