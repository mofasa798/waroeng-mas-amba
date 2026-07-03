# AI_CONTEXT.md

# Grocery Store Management System

A simple, fast, and maintainable POS & inventory system for a small family-owned grocery store.

App Name: Waroeng Mas Amba

Main products:
- Household essentials
- Snacks (primary business)

Target users are non-technical people, especially older adults.

---

# Tech Stack

Frontend
- Next.js
- TypeScript
- Tailwind CSS
- shadcn/ui

Backend
- Laravel
- REST API

Database
- PostgreSQL

Deployment
- Railway

---

# Global Rules

- Prioritize simplicity.
- Minimize clicks.
- Large buttons.
- Responsive UI.
- Clean architecture.
- Reusable components.
- RESTful API.
- Never put business logic in the frontend.
- Always use database transactions for stock updates.
- Keep code readable over clever.

---

# Development Phases

## Phase 1 — Project Foundation ✅

**Status: Completed**

Goal:
Prepare a scalable project structure.

Tasks:
- Configure frontend/backend
- Authentication (Sanctum token-based API)
- User management (CRUD with admin/kasir roles)
- Database migrations (users, personal_access_tokens, role column)
- API routes (public + protected + admin)
- Environment configuration
- Middleware for admin authorization

Deliverable:
Working authentication and project skeleton.

### What was built
- `POST /api/register` & `POST /api/login` & `POST /api/logout` & `GET /api/user`
- `GET/POST/PUT/DELETE /api/users` (admin only)
- `is_admin` middleware for route protection
- `role` column (admin/kasir) on users table
- Default seeders: admin@waroeng.test / kasir@waroeng.test (password: password)

---

## Phase 2 — Product Management ✅

**Status: Completed**

Goal:
Manage store products.

Features:
- CRUD products
- Categories
- Barcode
- Cost price
- Selling price
- Initial stock
- Supplier assignment (column ready, feature in Phase 3)

Deliverable:
Complete product management.

### What was built
- `GET/POST /api/categories`, `PUT/DELETE /api/categories/{id}` — CRUD kategori
- `GET/POST /api/products`, `PUT/DELETE /api/products/{id}` — CRUD produk
- `GET /api/products/{id}/stock` — stok dihitung dari stock_movements
- Initial stock otomatis tercatat sebagai `stock_movement type=in` saat create product
- Stok dihitung dari SUM(in) - SUM(out), bukan kolom langsung
- Semua operasi stok pakai database transaction
- 8 kategori di-seed (Makanan Ringan, Minuman, dll.)
- `supplier_id` ada di kolom (nullable), FK akan aktif di Phase 3

---

## Phase 3 — Supplier Management ✅

**Status: Completed**

Goal:
Manage suppliers.

Features:
- CRUD suppliers
- Contact information (phone, address, notes)
- Purchase history (data structure ready, feature in Phase 4)

Deliverable:
Supplier module completed.

### What was built
- `GET/POST /api/suppliers`, `PUT/DELETE /api/suppliers/{id}` — CRUD supplier
- `GET /api/suppliers/{id}/products` — list produk dari supplier
- FK constraint `products.supplier_id → suppliers.id` (on delete set null)
- Relasi `belongsTo(Supplier)` di Product model
- Validasi `supplier_id` di create/update product (exists:suppliers)
- 3 supplier di-seed (PT Sinar Jaya Abadi, CV Maju Makmur, UD Berkah Jaya)

---

## Phase 4 — Inventory Management ✅

**Status: Completed**

Goal:
Build a reliable inventory system.

Features:
- Restock
- Stock adjustment
- Stock movement history

Rules:
- Never edit stock directly.
- Every stock change creates a Stock Movement record.

Deliverable:
Reliable inventory tracking.

### What was built
- `POST /api/products/{id}/restock` — tambah stok (type: in), wajib quantity, note opsional
- `POST /api/products/{id}/adjust-stock` — adjust stok (type: adjustment), quantity bisa negatif, wajib note. Admin only.
- `GET /api/stock-movements` — riwayat semua pergerakan stok, support filter `?product_id=`
- Semua operasi stok pakai database transaction
- Stok tetap dihitung dari SUM(in) - SUM(out) + SUM(adjustment)

---

## Phase 5 — POS (Cashier) ✅

**Status: Completed**

Goal:
Fast checkout process.

Features:
- Product search
- Barcode scanning
- Shopping cart
- Discount
- Checkout
- Receipt (optional)

Priority:
Speed over appearance.

Deliverable:
Fully functional cashier system.

### What was built
- `GET /api/products/search?q=` — search by name (like) or barcode (exact)
- `POST /api/checkout` — transaksi dalam 1 DB transaction
- `GET /api/sales/{sale}` — invoice detail
- Tables: `sales` (invoice_number, total, discount, grand_total, user_id), `sale_items` (sale_id, product_id, quantity, price)
- Invoice format: `INV-YYYYMMDD-XXXX` (auto-increment per hari)
- Validasi stok cukup sebelum checkout (abort 422 jika kurang)
- Stock movement type `out` untuk setiap item terjual
- Diskon dalam rupiah integer

---

## Phase 6 — Sales History ✅

**Status: Completed**

Goal:
Track every sale.

Features:
- Sales list
- Sale details
- Invoice lookup
- Daily transactions

Deliverable:
Complete sales history.

### What was built
- `GET /api/sales` — list transaksi paginated, filter by `?date=`, `?from=&to=`
- `GET /api/sales/lookup?invoice=INV-...` — cari by invoice number
- `GET /api/sales/daily-summary?date=YYYY-MM-DD` — ringkasan harian (total transaksi, revenue, diskon, items sold)
- `GET /api/sales/{sale}` — detail transaksi (dari Phase 5)
- Semua endpoint eager-load relasi (items, product, user)
- Urut dari terbaru

---

## Phase 7 — Reports ✅

**Status: Completed**

Goal:
Generate business reports.

Reports:
- Daily
- Weekly
- Monthly
- Yearly
- Gross profit
- Best-selling products
- Slow-moving products

Deliverable:
Business reporting system.

### What was built
- `GET /api/reports/summary?period=daily|weekly|monthly|yearly&date=` — period summary with gross profit
- `GET /api/reports/best-sellers?period=daily|weekly|monthly&date=` — top 10 by qty sold
- `GET /api/reports/slow-movers?days=30` — products with least sales
- Gross profit: SUM((selling_price - cost_price) × qty) via join sale_items → products
- ReportController terpisah untuk clean separation

---

## Phase 8 — Smart Inventory

Goal:
Help store owners make decisions.

Features:
- Low stock alerts
- Suggested restock
- Dead stock detection
- Expiring products

Deliverable:
Inventory insights.

---

## Phase 9 — Optimization

Goal:
Improve usability.

Tasks:
- Keyboard shortcuts
- Better search
- Performance optimization
- Responsive improvements
- Error handling
- Loading states

Deliverable:
Production-ready UX.

---

## Phase 10 — Production

Goal:
Deploy stable application.

Tasks:
- Testing
- Bug fixing
- Backup strategy
- Logging
- Deployment
- Documentation

Deliverable:
Stable production release.

---

# Database Principles

Recommended tables:

- users
- products
- categories
- suppliers
- purchases
- purchase_items
- sales
- sale_items
- stock_movements

Stock is calculated from stock movements.

Never manually modify stock values.

---

# UI Principles

Always:

- Simple
- Fast
- Accessible
- Large typography
- Large buttons
- Minimal colors
- Minimal dialogs

Avoid:

- Complex navigation
- Hidden actions
- Fancy animations
- Tiny clickable areas

The application should feel like a modern POS, not an admin dashboard.

---

# AI Instructions

When generating code:

- Only implement the current phase.
- Do not generate future-phase features unless requested.
- Prefer maintainable solutions.
- Keep files modular.
- Follow Laravel and Next.js best practices.
- Reuse existing components.
- Avoid unnecessary dependencies.
- Write production-ready code.
- Explain important architectural decisions briefly.