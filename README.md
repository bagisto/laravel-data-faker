# Bagisto datafaker
[![Total Downloads](https://img.shields.io/packagist/dt/bagisto/laravel-datafaker.svg?style=flat-square)](https://packagist.org/packages/bagisto/laravel-datafaker)

### 1. Introduction:

This module allows you to generate fake data that can be populated into the database during development for testing purposes.  

* User can create customers
* User can create categories
* User can create products (Simple, Virtual, Downloadable and Configurable)

### 2. Requirements:

* **Bagisto**: master

### 3. Installation:

```sh
composer require bagisto/laravel-datafaker
```

* Run the following command below to complete the setup

```sh
php artisan config:cache
```
```sh
php artisan bagisto:fake
```
-> Select suitable option to create records

->That's all 