# Laravel Lightning Cache
A package to extend Laravel's cache functionality

- [Installation](#installation)


## Installation

To install through composer you can either use `composer require ferrisbane/lightning-cache` (while inside your project folder) or include the package in your `composer.json`.

```php
"ferrisbane/lightning-cache": "0.1.*"
```

Then run either `composer install` or `composer update` to download the package.

To use the package with Laravel 5 add the service provider to the list of service providers in `config/app.php`.

```php
'providers' => [
    ...

    Ferrisbane\Cache\Laravel5ServiceProvider::class

    ...
];
```
