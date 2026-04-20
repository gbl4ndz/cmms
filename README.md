# CMMS — Computerized Maintenance Management System

A production-ready CMMS built with **Laravel 12** (API) and **Vue 3** (SPA frontend).

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2, MySQL |
| Auth | Laravel Sanctum (token-based) |
| Frontend | Vue 3 (Composition API), Vite, Tailwind CSS v4 |
| State | Pinia |
| HTTP | Axios |
| Routing | Vue Router 4 |

---

## Modules

- **Locations** — physical sites and facilities
- **Areas** — zones within locations
- **Contractors** — external service providers
- **Categories** — asset and parts classification
- **Assets** — equipment with file/image attachments
- **Parts & Inventory** — spare parts with low-stock alerts
- **Work Orders** — corrective, preventive, inspection, emergency
- **Meters** — usage tracking with preventive maintenance auto-trigger
- **Users** — admin and user roles

---

## Getting Started

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+

### 1. Clone

```bash
git clone https://github.com/gbl4ndz/cmms.git
cd cmms
```

### 2. Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_DATABASE=cmms
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE cmms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations and seed demo data
php artisan migrate --seed

# Create storage symlink
php artisan storage:link

# Start the API server
php artisan serve --port=8001
```

### 3. Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

Open **http://localhost:5173**

---

## Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@cmms.local | password |
| Technician | tech@cmms.local | password |

---

## Project Structure

```
cmms/
├── backend/                  # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/  # Thin REST controllers
│   │   │   └── Requests/     # Form validation per endpoint
│   │   ├── Models/           # Eloquent models
│   │   ├── Services/         # Business logic
│   │   │   ├── WorkOrderService.php
│   │   │   ├── MeterService.php
│   │   │   ├── MediaService.php
│   │   │   └── PreventiveMaintenanceService.php
│   │   ├── Policies/         # Authorization
│   │   └── Observers/        # Model event hooks
│   ├── database/
│   │   ├── migrations/       # 16 migrations
│   │   └── seeders/
│   └── routes/api.php        # 56 API routes
│
└── frontend/                 # Vue 3 SPA
    └── src/
        ├── services/         # Axios resource wrappers
        ├── stores/           # Pinia (auth)
        ├── router/           # Vue Router with auth guards
        ├── components/
        │   ├── common/       # BaseInput, BaseModal, FileUpload…
        │   └── layout/       # Sidebar, Navbar, AppLayout
        └── views/            # One directory per module
```

---

## API Overview

All endpoints require a Bearer token (except `/api/login`).

```
POST   /api/login
POST   /api/logout
GET    /api/user

GET|POST          /api/assets
GET|PUT|DELETE    /api/assets/{id}
POST              /api/assets/{id}/media

GET|POST          /api/work-orders
GET|PUT|DELETE    /api/work-orders/{id}
PATCH             /api/work-orders/{id}/status
POST              /api/work-orders/{id}/comments
POST              /api/work-orders/{id}/parts
DELETE            /api/work-orders/{id}/parts/{partId}
POST              /api/work-orders/{id}/media

GET|POST          /api/meters
GET|DELETE        /api/meters/{id}
POST              /api/meters/{id}/readings
POST              /api/meters/{id}/reset-baseline

GET|POST|PUT|DELETE  /api/locations
GET|POST|PUT|DELETE  /api/areas
GET|POST|PUT|DELETE  /api/contractors
GET|POST|PUT|DELETE  /api/categories
GET|POST|PUT|DELETE  /api/parts
GET|POST|PUT|DELETE  /api/users

GET  /api/dashboard
```

---

## Key Features

### Work Order Status Flow
```
open → in_progress → on_hold → closed
              ↑___________|
```
Transitions are enforced server-side. Each change is logged with a timestamp and optional comment.

### Preventive Maintenance Auto-trigger
When a meter reading crosses its maintenance interval, a preventive work order is automatically created and surfaced in the UI.

### File Uploads
Assets and work orders support polymorphic file attachments (images, PDFs, documents) via drag-and-drop or click-to-browse. Files are stored on the `public` disk with direct URLs.

### Inventory Deduction
Adding a part to a work order deducts from `quantity_on_hand` in a database transaction. Removing the part returns the stock. Low-stock alerts appear on the dashboard.

---

## License

MIT
