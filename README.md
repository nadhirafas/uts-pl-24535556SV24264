Book Management
Book Management adalah aplikasi web sederhana untuk mengelola data buku. Aplikasi ini dibuat menggunakan Laravel sebagai backend, Vue.js sebagai frontend, dan MySQL sebagai database.

Fitur
Login menggunakan akun yang sudah disiapkan.
Menampilkan daftar buku dan detail buku.
Menambahkan data buku.
Mengubah data buku.
Menghapus data buku.
Melindungi endpoint API menggunakan token autentikasi.
Teknologi
Laravel untuk REST API.
Vue.js dan Axios untuk frontend dan komunikasi dengan API.
MySQL untuk menyimpan data buku.
Docker dan Docker Compose untuk menjalankan aplikasi.
FrankenPHP untuk menjalankan backend.
GitHub Actions untuk menjalankan pengujian, build frontend, dan deployment image Docker.
GitHub Container Registry (GHCR) untuk menyimpan image Docker.
Menjalankan Aplikasi
Pastikan Docker Desktop sudah berjalan. Siapkan file .env berdasarkan .env.example, lalu isi konfigurasi yang dibutuhkan, termasuk APP_KEY dan konfigurasi database.

Jalankan perintah berikut dari folder utama repository:

docker compose up -d --build
Periksa status container:

docker compose ps
Jika semua service sudah berjalan, buka aplikasi melalui browser:

Frontend: http://localhost:8080
Backend API: http://localhost:8000
Untuk menghentikan aplikasi, jalankan:

docker compose down
Data database disimpan pada named volume Docker. Karena itu, jangan gunakan docker compose down -v jika data masih ingin dipertahankan.

Pengujian
Pengujian backend dapat dijalankan dari folder backend menggunakan perintah:

php artisan test
Request API juga tersedia dalam file postman_collection.json di folder utama repository dan dapat diimpor melalui Postman.

Repository
Kode aplikasi dan workflow GitHub Actions tersedia di:

https://github.com/nadhirafas/uts-pl-24535556SV24264

⋮⋮⋮⋮⋮⋮⋮⋮
