### 1. Introduction:

Data Faker module provides fake Data for bagisto . This allows us to generate fake data that can be populated into the database during development for testing purposes.

By using this, you can generate fake data for the bagisto product and customer and categories.

It packs some feature:

* The user can create Product fake data.

* The user can create Users fake data.

* The user can create some Category fake data.

* The user can create Product Category fake data.

### 2. Requirements:

* **Bagisto**: v0.1.6 or higher.

### 3. Installation:

* Unzip the respective extension zip and then merge "packages" folders into project root directory.
* Goto config/app.php file and add following line under 'providers'

~~~
Webkul\DataFaker\Providers\DataFaker::class
~~~

* Goto composer.json file and add following line under 'psr-4'

~~~
"Webkul\\DataFaker\\": "packages/Webkul/DataFaker/src"
~~~

* Run these commands below to complete the setup

~~~
composer dump-autoload
~~~

~~~
composer require mbezhanov/faker-provider-collection
~~~

~~~
php artisan migrate
~~~

~~~
php artisan db:seed --class="Webkul\DataFaker\Database\Seeders\DatabaseSeeder"
~~~

~~~
php artisan vendor:publish

-> Press 0 and then press enter to publish all assets and configurations.
~~~

> now execute the project on your specified domain.
