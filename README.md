# Emerald Housing (Emerald-BH)

Multi-role property rental platform built with Laravel and Vue. Connects tenants, land owners, and community mediators through approval workflows, digital agreements, AR property previews, and monthly rent tracking.

## Repository description (for GitHub)

> Laravel + Inertia/Vue rental platform with five roles, approval workflows, community mediation, AR room previews, and land-owner rent payment tracking.

## Features

- **Five roles:** Customer, Land Owner, Community Mediator, Super Admin, Dev Admin
- **Property lifecycle:** Submit → Super Admin approve/reject → customer explore
- **Rental flow:** Request rent → assign mediator → activate agreement → digital signatures
- **Land owner tools:** AR model upload (GLB/glTF/USDZ via Google Model Viewer), rented-property tenant view, monthly payment tracker
- **Community portal:** Activate rentals, print contact sheets, dispute resolution
- **UI:** Role-themed dashboards, glassmorphism cards, toast notifications, public landing page

## Tech stack

| Layer    | Stack                                      |
|----------|--------------------------------------------|
| Backend  | PHP 8.3+, Laravel 13, Fortify, Inertia 3   |
| Frontend | Vue 3, TypeScript, Vite 5, Tailwind CSS 3  |
| Database | MySQL (default)                            |

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ (Laragon: `node-v18`)
- MySQL 8.0+

## Installation

```bash
git clone <your-repo-url>
cd emerald-bh

composer install
cp .env.example .env
php artisan key:generate

# Configure DB_* in .env, then:
php artisan migrate --seed
php artisan storage:link

npm install
npm run dev          # terminal 1
php artisan serve    # terminal 2
```

Production assets: `npm run build`

## Demo accounts

All passwords: `password`

| Role         | Email                   |
|--------------|-------------------------|
| Customer     | customer@emerald.test   |
| Land Owner   | owner@emerald.test      |
| Community    | community@emerald.test  |
| Super Admin  | admin@emerald.test      |
| Dev Admin    | dev@emerald.test        |

Public site: `/` · Login: `/login`

## Rental workflow (summary)

1. Land owner submits a property → `pending`
2. Super Admin approves → `approved` (visible to customers)
3. Customer requests to rent → `requested`
4. Super Admin assigns a community mediator → `community_review`
5. Community activates the rental → `active`, property → `rented`
6. Land owner tracks monthly payments from the rented listing detail page

## Project structure

```
app/Http/Controllers/     # Role dashboards & workflows
app/Services/             # Rent payment schedule, audit logging
database/migrations/      # Schema & workflow fields
database/seeders/         # EmeraldSeeder (demo data)
resources/js/pages/       # Inertia Vue pages (emerald/*, auth/*)
routes/emerald.php        # Role-based routes
MARKDOWN.md               # Full spec & design reference
```

## License

MIT
