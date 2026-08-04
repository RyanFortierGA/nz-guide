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

## Deploy on Laravel Forge

Repo: `RyanFortierGA/nz-guide` (branch `main`) — already pushed.

### 1. Create the site
1. Forge → your server → **New Site**
2. Domain (e.g. `guide.yourdomain.com`)
3. Project type **Laravel**, web directory `/public`
4. Install from GitHub: `RyanFortierGA/nz-guide`, branch `main`

### 2. Environment
In **Environment**, set at least:

```env
APP_NAME="Explore Aotearoa"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nz_guide
DB_USERNAME=forge
DB_PASSWORD=...

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Create the MySQL database in Forge first (or use an existing one). Generate `APP_KEY` with **Generate App Key** if empty.

### 3. Deploy script
Use the contents of [`forge-deploy.sh`](forge-deploy.sh) (includes `npm ci && npm run build` for Inertia/Vue).

Ensure the server has **Node 20+** (Forge → Server → **Node** / Meta → update Node if needed). This app wants Node 22 (see `.nvmrc`).

### 4. First deploy + seed
1. Deploy once (migrate runs in the script)
2. SSH in (or Forge → Commands) and seed destinations:

```bash
cd ~/your-domain.com
php artisan db:seed --force
```

(Or only `php artisan db:seed --class=LocationSeeder --force` if you don’t want the demo user.)

### 5. SSL + Quick Deploy
- Enable **LetsEncrypt** certificate
- Turn on **Quick Deploy** so pushes to `main` redeploy

Demo login after full seed: `demo@aotearoa.test` / `password` — change or remove in production.
