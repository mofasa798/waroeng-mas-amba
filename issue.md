# Phase 1 — Project Foundation

> Goal: Siapkan struktur project yang scalable. Deliverable: autentikasi & skeleton siap pakai.

---

## Backend (Laravel)

### 1.1 — Database Setup
- [ ] Ganti `DB_CONNECTION` di `.env` dari `sqlite` ke `mysql`
- [ ] Buat database di Laragon (nama: `waroeng_mas_amba`)
- [ ] Uncomment & isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] Jalankan `php artisan migrate` — pastikan sukses

### 1.2 — Auth API (Sanctum)
- [ ] Install Sanctum: `composer require laravel/sanctum`
- [ ] Publish config & migrate Sanctum
- [ ] Setup token-based auth guard di `config/auth.php`
- [ ] Buat `POST /api/register` — validasi: name, email, password. Return user + token.
- [ ] Buat `POST /api/login` — validasi: email, password. Return user + token.
- [ ] Buat `POST /api/logout` — revoke current token. Harus login.
- [ ] Buat `GET /api/user` — return user yang sedang login.
- [ ] Buat `routes/api.php` untuk grouping route di atas.
- [ ] Aktifkan CORS (package `fruitcake/laravel-cors` sudah ada).

### 1.3 — User Management API (Admin Only)
- [ ] Tambah kolom `role` (enum: admin/kasir) ke tabel users via migration baru.
- [ ] Buat `GET /api/users` — list semua user. Admin only.
- [ ] Buat `POST /api/users` — tambah user baru. Admin only.
- [ ] Buat `PUT /api/users/{id}` — edit user. Admin only.
- [ ] Buat `DELETE /api/users/{id}` — hapus user. Admin only.
- [ ] Buat middleware `is_admin` untuk proteksi route.

---

## Frontend (Next.js)

### 1.4 — Project Setup
- [ ] `npx create-next-app@latest frontend --typescript --tailwind --eslint --app --src-dir`
- [ ] Install shadcn/ui: `npx shadcn@latest init`
- [ ] Install axios untuk API client
- [ ] Buat file `.env.local` dengan `NEXT_PUBLIC_API_URL=http://localhost:8000/api`
- [ ] Setup folder structure: `app/`, `components/`, `lib/`, `hooks/`

### 1.5 — Auth Pages
- [ ] Halaman Login — form email + password → panggil `/api/login`. Simpan token di cookie/localStorage.
- [ ] Halaman Register (opsional) — form name, email, password → panggil `/api/register`.
- [ ] Middleware Next.js — redirect ke `/login` jika belum login.
- [ ] Tombol Logout di header.

### 1.6 — Shared Layout
- [ ] Layout utama dengan sidebar navigasi sederhana (Dashboard, Produk, dll placeholder).
- [ ] Header dengan nama user & tombol logout.
- [ ] Desain: large buttons, large typography, minimal color. Sesuai prinsip UI di AI_CONTEXT.md.

### 1.7 — API Client
- [ ] Buat `lib/api.ts` — axios instance dengan base URL, auto-attach token, auto-redirect ke login jika 401.

### 1.8 — User Management Page (Admin)
- [ ] Halaman `/users` — tabel list user dengan tombol tambah/edit/hapus.
- [ ] Halaman hanya bisa diakses admin.

---

## Acceptance Criteria

- [ ] `php artisan migrate` sukses ke MySQL
- [ ] Register & login via API return token
- [ ] Admin bisa CRUD user via API
- [ ] Next.js login page berfungsi → setelah login redirect ke `/dashboard`
- [ ] Layout muncul dengan sidebar & header
- [ ] Token tersimpan dan auto-attach di setiap request
- [ ] Logout berfungsi

---

## Notes

- Jangan buat fitur phase lain (produk, supplier, dll).
- Fokus: struktur, auth, user management.
- Backend: RESTful, validasi input, return JSON.
- Frontend: simple, tombol besar, minim warna.
