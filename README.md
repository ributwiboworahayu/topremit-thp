# Persiapan Proyek:

1. Pembuat proyek Laravel 9.0 menggunakan versi PHP 8.1.
2. Inisialisasi sistem manajemen basis data dengan MySQL.
3. Implementasikan autentikasi pengguna dan otorisasi menggunakan Laravel Passport

# Desain API:

1. Rancangan API endpoints untuk fitur-fitur yang diminta.
2. Struktur Request dan Respons REST API dalam format JSON.

# Implementasi Fitur-fitur Utama:

## a. Verifikasi Profil Pengguna:

- Endpoint untuk menyimpan profil pengguna.
- `/api/v1/auth/verify-profile`
- Set middleware `auth:api`

## b. Transaksi Pengiriman Uang:

- Desain endpoint untuk memfasilitasi transaksi pengiriman uang.
- `/api/v1/exchange/rates` untuk cek harga konversi IDR
- `/api/v1/exchange/send` untuk user membuat transaksi (voucher dapat diterapkan disini)
- `/api/v1/gateway/payment` untuk payment gateway api mengupdate transaksi (harga konversi diambil dari openapi
  milik https://app.exchangerate-api.com/)
- Validasi data yang diterima, termasuk alamat penerima sesuai persyaratan lokal AUD.
- Gunakan mekanisme transaksi database yang aman dan terjamin.

## c. Pembayaran dan Poin Pengguna:

- Implementasikan sistem pembayaran untuk transaksi.
- Sistem diterapkan setelah payment dilakukan dan api endpoint payment gateway user mendapatkan point reward yang bisa
  diredeem dengan voucher
- `/api/v1/voucher` untuk list voucher
- `/api/v1/voucher/redeem` untuk redeem voucher

## d. Update Transaksi:

- Notifikasi dikirim melalui email dengan queue agar tidak memberlambat kinerja api
