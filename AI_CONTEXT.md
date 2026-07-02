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

## Phase 2 — Product Management

Goal:
Manage store products.

Features:
- CRUD products
- Categories
- Barcode
- Cost price
- Selling price
- Initial stock
- Supplier assignment

Deliverable:
Complete product management.

---

## Phase 3 — Supplier Management

Goal:
Manage suppliers.

Features:
- CRUD suppliers
- Contact information
- Purchase history

Deliverable:
Supplier module completed.

---

## Phase 4 — Inventory Management

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

---

## Phase 5 — POS (Cashier)

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

---

## Phase 6 — Sales History

Goal:
Track every sale.

Features:
- Sales list
- Sale details
- Invoice lookup
- Daily transactions

Deliverable:
Complete sales history.

---

## Phase 7 — Reports

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