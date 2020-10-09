# Bagisto
[![Latest Version on Packagist](https://img.shields.io/packagist/v/bagisto/laravel-data-faker.svg?style=flat-square)](https://packagist.org/packages/bagisto/laravel-data-faker)
[![Total Downloads](https://img.shields.io/packagist/dt/bagisto/laravel-data-faker.svg?style=flat-square)](https://packagist.org/packages/bagisto/laravel-data-faker)

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
composer require bagisto/laravel-datafaker
```

* Run the following command below to complete the setup

```sh
php artisan db:seed --class="Webkul\DataFaker\Database\Seeders\DatabaseSeeder"
```