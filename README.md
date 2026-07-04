# Waroeng Mas Amba

> A simple, fast, and maintainable POS & inventory system for a small family-owned grocery store.

**Tech Stack:** Laravel 13 (backend) + Next.js 15 / React 19 (frontend) + MySQL (local) / PostgreSQL (production) + Railway (backend) + Vercel (frontend)

---

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/       # API controllers
│   │   │   ├── AuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── InventoryInsightController.php
│   │   │   ├── PosController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ReportController.php
│   │   │   ├── StockMovementController.php
│   │   │   ├── SupplierController.php
│   │   │   └── UserController.php
│   │   └── Middleware/
│   │       └── IsAdmin.php        # Admin role middleware
│   ├── Models/                    # Eloquent models
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Sale.php
│   │   ├── SaleItem.php
│   │   ├── StockMovement.php
│   │   ├── Supplier.php
│   │   └── User.php
│   └── Providers/
├── config/                        # Laravel config files
├── database/
│   ├── migrations/                # Database migrations
│   ├── factories/                 # Model factories
│   └── seeders/
│       ├── DatabaseSeeder.php     # Creates admin user + calls seeders
│       ├── CategorySeeder.php
│       ├── SupplierSeeder.php
│       └── ProductSeeder.php
├── frontend/                      # Next.js 15 frontend (App Router)
│   └── src/
│       ├── app/
│       │   ├── (dashboard)/       # Authenticated dashboard group
│       │   │   ├── categories/
│       │   │   ├── inventory/
│       │   │   ├── pos/
│       │   │   ├── products/
│       │   │   ├── sales/
│       │   │   ├── suppliers/
│       │   │   ├── layout.tsx
│       │   │   └── page.tsx
│       │   ├── login/
│       │   ├── register/
│       │   ├── globals.css
│       │   ├── layout.tsx
│       │   └── providers.tsx
│       ├── components/
│       │   ├── Header.tsx
│       │   ├── Sidebar.tsx
│       │   └── Toast.tsx
│       └── lib/
│           ├── api.ts             # Axios instance with auth interceptor
│           ├── auth.tsx           # Auth context & provider
│           └── utils.ts           # Utility helpers
├── routes/
│   ├── api.php                    # API routes
│   └── web.php                    # Web routes (minimal)
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── TestCase.php
├── Procfile                       # Railway deployment
```

---

## Local Development

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- MySQL (local via Laragon) atau PostgreSQL (production)

### Setup Backend

```bash
# Clone & install dependencies
git clone https://github.com/mofasa798/waroeng-mas-amba.git
cd waroeng-mas-amba
composer install
cp .env.example .env
php artisan key:generate

# Sesuaikan .env untuk local (MySQL):
#   DB_CONNECTION=mysql
#   DB_HOST=127.0.0.1
#   DB_PORT=3306
#   DB_DATABASE=waroeng_mas_amba
#   DB_USERNAME=root
#   DB_PASSWORD=

# Buat database & jalankan migrasi
php artisan migrate --seed
php artisan serve --port=8000
```

### Setup Frontend

```bash
cd frontend
cp .env.local.example .env.local   # or create it manually
npm install
npm run dev
```

**Frontend `.env.local`:**
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

### Run Tests

```bash
# All tests
php artisan test

# Specific test
php artisan test --filter AuthTest
```

---

## Default Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@waroeng.test | password |
| Kasir | kasir@waroeng.test | password |

---

## API Endpoints

### Auth
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | Public | Register new user |
| POST | `/api/login` | Public | Login, returns token |
| POST | `/api/logout` | auth:sanctum | Revoke token |
| GET | `/api/user` | auth:sanctum | Get authenticated user |

### Users (Admin Only)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/users` | List all users |
| POST | `/api/users` | Create user |
| PUT | `/api/users/{id}` | Update user |
| DELETE | `/api/users/{id}` | Delete user |

### Categories
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/categories` | List categories |
| POST | `/api/categories` | Create category |
| PUT | `/api/categories/{id}` | Update category |
| DELETE | `/api/categories/{id}` | Delete category |

### Products
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/products` | List products (with stock) |
| POST | `/api/products` | Create product |
| PUT | `/api/products/{id}` | Update product |
| DELETE | `/api/products/{id}` | Delete product |
| GET | `/api/products/search?q=` | Search by name/barcode |
| GET | `/api/products/{id}/stock` | Get current stock |
| POST | `/api/products/{id}/restock` | Add stock |
| POST | `/api/products/{id}/adjust-stock` | Adjust stock (admin) |

### Suppliers
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/suppliers` | List suppliers |
| POST | `/api/suppliers` | Create supplier |
| PUT | `/api/suppliers/{id}` | Update supplier |
| DELETE | `/api/suppliers/{id}` | Delete supplier |
| GET | `/api/suppliers/{id}/products` | Supplier's products |

### POS / Sales
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/checkout` | Process sale |
| GET | `/api/sales` | List sales (filter: `?date=`, `?from=&to=`) |
| GET | `/api/sales/{id}` | Sale detail |
| GET | `/api/sales/lookup?invoice=INV-...` | Find by invoice |
| GET | `/api/sales/daily-summary?date=` | Daily summary |

### Stock Movements
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/stock-movements` | List movements (filter: `?product_id=`) |

### Reports
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/reports/summary` | Period summary (`?period=daily\|weekly\|monthly\|yearly`) |
| GET | `/api/reports/best-sellers` | Top products (`?period=daily\|weekly\|monthly`) |
| GET | `/api/reports/slow-movers` | Dead stock (`?days=30`) |

### Inventory Insights
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/inventory/low-stock` | Low stock alert (`?threshold=10`) |
| GET | `/api/inventory/suggested-restock` | Restock recommendations |
| GET | `/api/inventory/dead-stock` | Dead stock (`?days=90`) |

---

## Deployment

### Backend — Railway

1. Buat akun Railway dan hubungkan GitHub repo
2. Buat PostgreSQL database di Railway
3. Set environment variables di Railway dashboard:

```
APP_ENV=production
APP_KEY=<generate dengan: php artisan key:generate --show>
APP_URL=https://your-backend.railway.app
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=<your-railway-pg-host>
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=<your-railway-pg-password>
DB_SSLMODE=require
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
FRONTEND_URL=https://your-frontend.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-frontend.vercel.app
```

4. Deploy — Railpack auto-detects Laravel dari `composer.json` dan `Procfile`
5. Jalankan migrasi via Railway CLI atau dashboard shell:
   ```
   php artisan migrate --force
   php artisan db:seed --force
   ```

### Frontend — Vercel

1. Buat akun Vercel dan hubungkan GitHub repo (arahkan root ke `frontend/`)
2. Set environment variable di Vercel dashboard:
   ```
   NEXT_PUBLIC_API_URL=https://your-backend.railway.app/api
   ```
3. Vercel auto-detects Next.js — build & deploy otomatis tiap push

---

## Database Principles

- Stock is **never stored directly** — calculated from `stock_movements` table
- Every stock change creates a `StockMovement` record (type: `in`, `out`, or `adjustment`)
- Stock adjustments are **admin-only** (protected by `is_admin` middleware)
- All stock operations use **database transactions**
- Prices stored as **integers** (rupiah, e.g. 10000 = Rp 10.000)
- Sale prices are **snapshots** at time of transaction (stored in `sale_items`)

---

## Key Dependencies

### Backend (Laravel 13)
| Package | Purpose |
|---|---|
| `laravel/sanctum` ^4.3 | API token authentication |
| `laravel/tinker` ^3.0 | Interactive REPL |

### Frontend (Next.js 15)
| Package | Purpose |
|---|---|
| `react` / `react-dom` ^19 | UI framework |
| `axios` ^1.7 | HTTP client |
| `lucide-react` ^0.460 | Icon library |
| `tailwindcss` ^4.0 | Utility-first CSS |
| `clsx` + `tailwind-merge` + `class-variance-authority` | Styling utilities |

---

## License

MIT
