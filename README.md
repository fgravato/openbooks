# OpenBooks

Cloud-based invoicing and accounting platform scaffolded with Laravel 12, PHP 8.5, Vue 3, Inertia, Tailwind, and Vite.

## Stack

- PHP 8.5 + Laravel 12
- Vue 3 + Inertia.js + TypeScript + Pinia
- Tailwind CSS
- MySQL 8 + Redis + Meilisearch
- Docker Compose for local development

## Quick Start (Local)

1. Install dependencies:

```bash
composer install
npm install
```

2. Prepare environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Start development services:

```bash
docker-compose up -d
php artisan migrate
npm run dev
php artisan serve
```

4. Open app:

`http://localhost:8000`

## Docker Services

- `app`: PHP-FPM 8.5
- `nginx`: Web server on port `8000`
- `mysql`: MySQL 8 on port `3306`
- `redis`: Redis 7 on port `6379`
- `meilisearch`: Search engine on port `7700`

## Project Structure

- `app/Domains/*`: DDD bounded contexts
- `app/Traits`: shared model traits
- `app/Enums`: global enums
- `app/Scopes`: global query scopes
- `resources/js`: Vue + Inertia frontend
- `tests/Unit/Domains`: domain-aligned unit tests

## Quality Commands

```bash
php artisan test
./vendor/bin/pest
./vendor/bin/phpstan analyse
./vendor/bin/pint --test
```
