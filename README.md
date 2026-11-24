# Ghost Stories

A class project for sharing ghost stories about places. Built with PHP, Laravel, Blade and SQLite, following the structure used in [blaug](https://github.com/ndeblauw/blaug).

This version uses Laravel 12 and PHP 8.4 to run with the installed Herd version. The class repository now uses Laravel 13 and PHP 8.5.

## Run locally

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
herd link ghost-stories
```

Open http://ghost-stories.test. No frontend build is needed.

## Tests

```sh
php artisan test
```
