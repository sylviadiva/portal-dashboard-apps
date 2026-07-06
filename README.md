# Portal Dashboard Aplikasi Internal

Portal terpusat untuk mengakses aplikasi internal perusahaan sesuai hak akses masing-masing user (departemen, role, dan/atau spesifik user).

**Stack:** Laravel 12 (API) + Vue 3 (Vite, Composition API) + PostgreSQL

---

## 1. Cara Menjalankan Aplikasi

### Prasyarat
- PHP ^8.2 dengan extension `pdo_pgsql`, `pgsql`, `zip`, `mbstring`, `openssl`
- Composer
- Node.js ^18 + npm
- PostgreSQL (database sudah dibuat, misal `portal_dashboard`)

### Langkah Instalasi

```bash
# 1. Clone repository & masuk ke folder project
git clone <repo-url>
cd portal-dashboard-apps

# 2. Install dependency PHP
composer install

# 3. Salin file environment & generate app key
cp .env.example .env
php artisan key:generate

# 4. Sesuaikan konfigurasi database di .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=portal_dashboard
DB_USERNAME=postgres
DB_PASSWORD=isi_password

# Pastikan juga baris ini (sudah default di .env.example):
SESSION_DRIVER=file

# 5. Jalankan migration + seeder (data dummy)
php artisan migrate:fresh --seed

# 6. Install dependency frontend
npm install
```

> ⚠️ Jangan lupa jalankan `php artisan key:generate` (langkah 3) — tanpa ini akan muncul error `MissingAppKeyException` saat membuka aplikasi di browser.

### Menjalankan Server (2 terminal berjalan bersamaan)

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (compile Vue)
npm run dev
```

Buka browser ke **http://localhost:8000**

### Akun Login Default (hasil seeder)

| Email | Password | Role |
|---|---|---|
| adhika@company.com | password | Admin |
| (user lain sesuai seeder) | password | User biasa |

> User baru yang dibuat lewat menu **Kelola User** otomatis mendapat password default `password` dan **wajib menggantinya** saat login pertama kali.

---

## 2. Struktur Singkat Project

### Backend (Laravel)

```
app/
├── Models/
│   ├── User.php            # relasi ke Department, Role, dan specific Application
│   ├── Department.php
│   ├── Role.php
│   └── Application.php
├── Services/
│   └── AccessService.php   # logic inti: UNION 3 sumber akses + dedup
├── Http/Controllers/Api/
│   ├── AuthController.php          # login, logout, me, reset-password
│   ├── DashboardController.php     # my-applications, effective-access
│   ├── ApplicationController.php   # CRUD aplikasi
│   ├── AccessController.php        # assign akses (dept/role/user)
│   ├── UserController.php          # CRUD user
│   └── ReferenceController.php     # data referensi utk dropdown

database/
├── migrations/    # departments, roles, users, applications, 3 pivot table
└── seeders/       # data dummy: 3 dept, 4 role, 9+ app, 10 user + overlap akses
```

### Frontend (Vue 3 + Vite)

```
resources/js/
├── api.js                  # axios instance + interceptor auto-attach token
├── router.js                # routing + auth guard (login/admin/reset-password)
├── store/auth.js             # state login sederhana (reactive, tanpa Pinia)
├── layouts/
│   └── AppLayout.vue        # sidebar navigasi (dibungkus di semua halaman ber-auth)
└── pages/
    ├── Login.vue
    ├── ResetPassword.vue     # wajib diisi user baru sebelum akses menu lain
    ├── Dashboard.vue          # dashboard user, render card aplikasi
    └── admin/
        ├── Applications.vue       # CRUD aplikasi + search/sort/pagination
        ├── AccessSettings.vue     # assign akses dept/role/user per aplikasi
        ├── Users.vue               # CRUD user + search/sort/pagination
        └── EffectiveAccess.vue    # "database view" — user + daftar aplikasinya
```

### Dokumentasi API
Tersedia terpisah di `Sylvia Divalona - API Documentation - Portal Dashboard.pdf` — berisi seluruh endpoint beserta contoh request/response.

---

## 3. Penjelasan Desain

### Skema Database

Hak akses aplikasi punya **3 sumber independen**, dimodelkan lewat 3 tabel pivot:

- `app_department` — aplikasi di-assign ke departemen (semua user di dept itu otomatis dapat akses)
- `app_role` — aplikasi di-assign ke role
- `app_user` — aplikasi di-assign langsung ke 1 user tertentu (specific access)

`users` sendiri punya `department_id` dan `role_id` sebagai foreign key biasa — jadi 2 dari 3 sumber akses itu "melekat" langsung ke identitas user.

### Effective Access Engine

`AccessService` menggabungkan ketiga sumber lewat **UNION ALL** di level query, lalu dedup di collection (`unique('id')`). Kenapa `UNION ALL` + dedup manual, bukan `UNION` murni? Karena `UNION` murni mensyaratkan struktur kolom identik persis antar sub-query, sementara `UNION ALL` + dedup by primary key lebih predictable lintas driver database (PostgreSQL/MySQL) dan lebih mudah dibaca.

Ada 2 method utama:
- `getEffectiveApplications($user)` — untuk 1 user (dipakai dashboard user yang sedang login), 3 query per panggilan.
- `getEffectiveAccessMatrix()` — untuk **semua** user sekaligus dalam **1 query gabungan** (bukan N+1), dipakai di halaman admin "Effective Access". Ini yang jadi dasar requirement "Database View — Effective Access".

### Force Password Change

User baru yang dibuat admin otomatis mendapat password default (`password`) dan flag `is_change_default_password = true`. Router guard di frontend akan memaksa user tersebut ke halaman `/reset-password` — tidak bisa mengakses menu manapun (termasuk lewat mengetik URL langsung) sampai password diganti.

### Struktur Kode

- **Controller** hanya menangani HTTP request/response dan validasi input.
- **Service** (`AccessService`) menampung business logic yang kompleks (query UNION), supaya thin controller dan logic bisa dites/dipakai ulang di tempat lain.
- **Model** menangani relasi Eloquent antar tabel.
- Di sisi Vue, tiap halaman admin mengikuti pola yang sama: `fetch → search (filter) → sort → paginate`, supaya konsisten dan mudah dipahami di setiap halaman.

### Autentikasi

Menggunakan **Laravel Sanctum** (Bearer Token) — dipilih karena aplikasi ini murni SPA + API, belum membutuhkan kompleksitas OAuth seperti Passport.

### Bonus yang Diimplementasikan

- ✅ Pagination & sorting (di halaman Kelola Aplikasi, Kelola User, Effective Access)
- ✅ Search/filter dinamis di semua halaman admin
- ✅ API design konsisten (REST, resource-based)
- ✅ Dokumentasi API lengkap

---

## 4. Troubleshooting

Berikut kendala yang ditemukan saat pengujian *clone & run* dari awal di device lain, beserta solusinya.

### `npm install` gagal dengan error `ERESOLVE` (konflik versi `vite`)

```
npm error Found: vite@8.1.3
npm error peer vite@"^7.0.0" from laravel-vite-plugin@2.1.0
```

**Penyebab:** versi `vite` di `package.json` tidak kompatibel dengan `laravel-vite-plugin`.

**Solusi:**
1. Pastikan `package.json` memakai `"vite": "^7.0.0"` (bukan `^8.x`)
2. Hapus `node_modules` dan `package-lock.json` kalau sudah sempat terbentuk sebelumnya:
   ```bash
   rm -rf node_modules package-lock.json
   ```
3. Install ulang:
   ```bash
   npm install
   ```

### Error `MissingAppKeyException` saat membuka aplikasi di browser

**Penyebab:** langkah `php artisan key:generate` belum dijalankan, sehingga `APP_KEY` di `.env` masih kosong.

**Solusi:**
```bash
php artisan key:generate
```

### Error `relation "sessions" does not exist` saat membuka aplikasi

```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "sessions" does not exist
```

**Penyebab:** `SESSION_DRIVER` di `.env` masih bernilai `database`, padahal project ini tidak membuat migration tabel `sessions` (autentikasi memakai token Sanctum, bukan session).

**Solusi:** pastikan di `.env`:
```env
SESSION_DRIVER=file
```
Lalu bersihkan cache config:
```bash
php artisan config:clear
```