# Ghost Stories

A class project for sharing ghost stories about places. Built with PHP, Laravel, Blade and SQLite, starting from the teacher's [starter pack](https://github.com/ndeblauw/starterpack) and following the conventions in [blaug](https://github.com/ndeblauw/blaug).

PHP 8.5 and Laravel 13, as supplied in the starter pack.

## Run locally

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
herd link ghost-stories
```

Open http://ghost-stories.test. The starter pack has a CDN fallback. To build the frontend locally, run `npm ci && npm run build`.

## Tests

```sh
php artisan test
```
