# Skena Coffee

Sistem Point of Sales (POS) dan Pemesanan Online untuk Skena Coffee. Dibangun menggunakan Laravel 11, Alpine.js, Tailwind CSS, dan integrasi Payment Gateway Midtrans.

## Fitur Utama

1. **Customer Front-end (Katalog & Checkout)**
   - Pilihan Menu dengan Kategori
   - Sistem Keranjang Belanja (Cart)
   - Checkout Langsung
   - Integrasi Pembayaran Midtrans (QRIS, GoPay, Bank Transfer, dll)
   - Lacak Status Pesanan Real-time

2. **Kasir Dashboard**
   - Manajemen Order Masuk Real-time
   - Notifikasi Pesanan Baru (Pop-up Dropdown)
   - Ubah Status Pesanan (Paid -> Making -> Ready -> Served)
   - Statistik Pendapatan Harian

3. **Admin Dashboard**
   - Manajemen User (Admin & Kasir)
   - Manajemen Kategori & Menu
   - Analytics Penjualan (Chart & Report)
   - Export Laporan PDF

## Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL/MariaDB

## Cara Instalasi

1. **Clone repository ini:**
   ```bash
   git clone https://github.com/dikaenwo/webwandi.git
   cd webwandi
   ```

2. **Install dependency PHP & Node.js:**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment:**
   ```bash
   cp .env.example .env
   ```
   Lalu sesuaikan konfigurasi database (`DB_*`) dan Midtrans (`MIDTRANS_*`) di file `.env`.

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Migrate dan Seed Database:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Proses ini akan membuat akun Admin, Owner, dan Kasir default beserta data dummy menu.*

6. **Build Asset Frontend:**
   ```bash
   npm run build
   ```

7. **Jalankan Local Server:**
   ```bash
   php artisan serve
   ```
   Aplikasi bisa diakses di `http://127.0.0.1:8000`.

## Kredensial Default (Dari Seeder)

- **Admin:** `admin@skenacoffee.id` / `password`
- **Owner:** `owner@skenacoffee.id` / `password`
- **Kasir:** `kasir@skenacoffee.id` / `password`

## Konfigurasi Midtrans

Pastikan Anda mendaftar di [Midtrans Sandbox](https://simulator.midtrans.com) untuk environment testing. Masukkan kredensial berikut ke dalam `.env`:

```env
MIDTRANS_MERCHANT_ID="ID_MERCHANT_ANDA"
MIDTRANS_CLIENT_KEY="CLIENT_KEY_ANDA"
MIDTRANS_SERVER_KEY="SERVER_KEY_ANDA"
MIDTRANS_IS_PRODUCTION=false
```

## Teknologi yang Digunakan

- [Laravel 11](https://laravel.com)
- [Tailwind CSS 4](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)
- [Lucide Icons](https://lucide.dev)
- [Chart.js](https://www.chartjs.org)
- [Midtrans Payment Gateway](https://midtrans.com)

## Lisensi
Hak Cipta © 2026 Skena Coffee. All rights reserved.
