Empari/Files
==========
Management Files for Laravel.

## Installation

### Composer

Execute the following command to get the latest version of the package:

```terminal
composer require empari/gfiles
```

### Laravel

In your `config/app.php` add `Empari\Files\FilesServiceProvider::class` to the end of the `providers` array:

```php
'providers' => [
    ...
    Empari\Files\FilesServiceProvider::class,
],
```

Publish Configuration

```shell
php artisan vendor:publish --provider "Empari/Files/FilesServiceProvider"
```

Migrate the new tables

```shell
php artisan migrate
```
