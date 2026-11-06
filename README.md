# Book Management

Book Management adalah aplikasi web sederhana untuk mengelola data buku. Aplikasi ini menggunakan Laravel sebagai backend, Vue.js sebagai frontend, dan MySQL sebagai database.

## Fitur Aplikasi

- Login menggunakan akun yang telah disiapkan.
- Menampilkan daftar buku dan detail buku.
- Menambahkan data buku.
- Mengubah data buku.
- Menghapus data buku.
- Melindungi endpoint API menggunakan token autentikasi.

## Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| Laravel | Mengembangkan REST API backend |
| Vue.js | Membangun tampilan frontend |
| Axios | Menghubungkan frontend dengan API |
| MySQL | Menyimpan data buku |
| Docker dan Docker Compose | Menjalankan aplikasi dalam container |
| FrankenPHP | Menjalankan backend |
| GitHub Actions | Menjalankan pengujian, build, dan deployment |
| GitHub Container Registry (GHCR) | Menyimpan image Docker |

## Cara Menjalankan Aplikasi

### 1. Persiapan

Pastikan Docker Desktop sudah berjalan. Siapkan file `.env` berdasarkan `.env.example`, kemudian sesuaikan konfigurasi yang dibutuhkan, termasuk `APP_KEY` dan database.

### 2. Menjalankan Aplikasi

Buka terminal di folder utama repository, kemudian jalankan:

```bash
docker compose up -d --build
```

### 3. Memeriksa Container

Untuk melihat status container, jalankan:

```bash
docker compose ps
```

### 4. Membuka Aplikasi

Jika seluruh service berjalan dengan baik, aplikasi dapat diakses melalui alamat berikut:

- **Frontend:** http://localhost:8080
- **Backend API:** http://localhost:8000

### 5. Menghentikan Aplikasi

Untuk menghentikan container, jalankan:

```bash
docker compose down
```

Database menggunakan named volume Docker agar datanya tetap tersimpan ketika container dihentikan. Hindari perintah `docker compose down -v` jika data database masih ingin dipertahankan.

## Pengujian

Pengujian backend dapat dijalankan dari folder `backend` menggunakan perintah:

```bash
php artisan test
```

Pengujian API juga dapat dilakukan melalui Postman. File `postman_collection.json` yang tersedia di folder utama repository dapat diimpor ke Postman untuk menggunakan request API yang telah disiapkan.

## CI/CD

GitHub Actions digunakan untuk menjalankan pengujian backend dan build frontend secara otomatis. Workflow deployment juga digunakan untuk membangun dan mengunggah image Docker ke GitHub Container Registry (GHCR).

## Repository

Kode sumber aplikasi dan konfigurasi workflow tersedia di repository berikut:

[Book Management – GitHub](https://github.com/nadhirafas/uts-pl-24535556SV24264)
