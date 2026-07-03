# Waroeng Mas Amba

> A simple, fast, and maintainable POS & inventory system for a small family-owned grocery store.

**Tech Stack:** Laravel (backend) + Next.js (frontend) + PostgreSQL + Railway

---

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/   # API controllers
│   │   └── Middleware/         # is_admin middleware
│   ├── Models/                 # Eloquent models
├── config/                     # Laravel config files
├── database/
│   ├── migrations/             # Database migrations
│   ├── factories/              # Model factories (testing)
│   └── seeders/                # Database seeders
├── frontend/                   # Next.js frontend app
├── routes/
│   ├── api.php                 # API routes
│   └── web.php                 # Web routes (minimal)
├── tests/                      # PHPUnit tests
└── Procfile                    # Railway deployment
```

---

## Local Development

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- SQLite (local) or PostgreSQL (production)

### Setup Backend

```bash
# Clone & install dependencies
git clone https://github.com/mofasa798/waroeng-mas-amba.git
cd waroeng-mas-amba
composer install
cp .env.example .env
php artisan key:generate

# Database (SQLite for local — already set in .env)
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

## Deployment (Railway)

### Backend

1. Create a Railway account and connect your GitHub repo
2. Create a PostgreSQL database on Railway
3. Set environment variables in Railway dashboard:

```
APP_ENV=production
APP_KEY=<generate with: php artisan key:generate --show>
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
FRONTEND_URL=https://your-frontend.railway.app
SANCTUM_STATEFUL_DOMAINS=your-frontend.railway.app
```

4. Deploy — Railway auto-detects Laravel via `nixpacks.toml`
5. Run migrations:
   `php artisan migrate --force`
   `php artisan db:seed --force`

### Frontend

1. Create a new Railway project for `frontend/`
2. Set environment variable:
   `NEXT_PUBLIC_API_URL=https://your-backend.railway.app/api`
3. Build command: `npm run build`
4. Start command: `npm start`

---

## Database Principles

- Stock is **never stored directly** — calculated from `stock_movements` table
- Every stock change creates a `StockMovement` record (type: in/out/adjustment)
- All stock operations use **database transactions**
- Prices stored as **integers** (rupiah, e.g. 10000 = Rp 10.000)
- Sale prices are **snapshots** at time of transaction

---

## License

MIT

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
