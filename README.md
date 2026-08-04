# Explore Aotearoa

Laravel + Inertia + Vue trip planner built from the Explore Aotearoa prototype.

## Features

- Filterable destination feed + Leaflet map (all 25 places + sub-spots from the prototype)
- Auth (register / login) via Laravel Breeze
- Personal trip plan sidebar — add and remove destinations
- Easy **Add location** form for signed-in users
- Location detail modal with **Airbnb** and **Google Flights** deep links (from your home airport)

## Setup

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run dev
```

In another terminal:

```bash
php artisan serve
```

Open http://127.0.0.1:8000

### Demo account

- Email: `demo@aotearoa.test`
- Password: `password`

## Adding locations

Signed-in users: **Add location** in the header, or seed via `database/seeders/locations.json` + `LocationSeeder`.

## Airbnb & flights

Airbnb and flight partners don’t offer free public listing/fare APIs for this use case, so the app opens curated search URLs:

- Airbnb near the destination (lat/lng + query)
- Google Flights from the user’s `home_airport` (default `AKL`) to the destination airport code

Set your home airport under **Profile**.

## Stack

Laravel 12, Inertia Vue 3, Breeze, Tailwind, Leaflet, SQLite by default.
