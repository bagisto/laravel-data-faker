# Bagisto datafaker
[![Total Downloads](https://img.shields.io/packagist/dt/bagisto/laravel-datafaker.svg?style=flat-square)](https://packagist.org/packages/bagisto/laravel-datafaker)

### 1. Introduction:

This module allows you to generate fake data that can be populated into the database during development for testing purposes.  

* user can create fake data from terminal.
* The user can create Product fake data.
* The user can create Customers fake data.
* The user can create some Category fake data.
* The user can create Product Category fake data.

### 2. Requirements:

* **Bagisto**: v1.3.2, v1.3.3.

### 3. Installation:

```sh
composer require bagisto/laravel-datafaker
```

* Run the following command below to complete the setup

```sh
php artisan config cache
```
```sh
php artisan seed:fake:data
```
->Select one of the option the data you want to seed.

->That's all 