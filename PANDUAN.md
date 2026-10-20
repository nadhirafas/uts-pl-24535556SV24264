# Panduan Pengerjaan UTS P1 — Laravel + Vue + GitHub Actions + Docker

Tema contoh: **Katalog Buku** (resource `books`). Ganti `NIM` dengan NIM kamu.
Semua berkas di zip ini adalah **kerangka**: berkas Laravel/Vue bawaan (vendor, package.json, dst.) kamu generate sendiri, lalu **timpa/tambahkan** berkas dari zip ini.

## Struktur akhir repo `uts-p1-NIM`

```
uts-p1-NIM/
├── .github/workflows/ci.yml
├── backend/            (Laravel + berkas dari zip)
├── frontend/           (Vue/Vite + berkas dari zip)
├── docker-compose.yml
├── .env.example        (.env asli TIDAK di-commit)
├── .gitignore
└── postman_collection.json
```

Prasyarat: PHP 8.3 + Composer, Node 22 + npm, Git, Docker Desktop, akun GitHub, Postman.

---

## Tahap 1 — Siapkan folder dan Laravel

```bash
mkdir uts-p1-NIM && cd uts-p1-NIM
git init -b main
composer create-project laravel/laravel backend
cd backend
php artisan install:api          # memasang Sanctum + routes/api.php + migrasi token
```

Cek `app/Models/User.php`: pastikan ada `use Laravel\Sanctum\HasApiTokens;` dan `use HasApiTokens, HasFactory, Notifiable;`. Jika belum, tambahkan.

## Tahap 2 — Salin kode backend dari zip (CPMKI a–c)

Salin ke `backend/` (timpa jika diminta):
`bootstrap/app.php` (bandingkan dulu dengan bawaan, intinya middleware JSON + `shouldRenderJsonWhen`), `routes/api.php`, `app/Http/Middleware/ForceJsonResponse.php`, `app/Http/Controllers/AuthController.php`, `BookController.php`, `app/Models/Book.php`, migrasi `create_books_table`, `BookFactory.php`, `DatabaseSeeder.php`, dan folder `tests/Feature/`.

Tambahkan ke `backend/.env` (lokal, tidak di-commit):

```
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=isi_sendiri
```

Jalankan:

```bash
php artisan migrate
php artisan db:seed
php artisan serve                # http://127.0.0.1:8000
```

(Lokal boleh memakai SQLite bawaan Laravel; di Docker memakai MySQL.)

Checklist kode status: login sukses 200 + token, `POST /api/books` **201**, validasi gagal **422**, tanpa token **401**.

## Tahap 3 — Feature test (CPMKI e)

```bash
php artisan test
```

Ada 8 test (syarat minimal 2). Pastikan `phpunit.xml` memakai `DB_CONNECTION=sqlite` dan `DB_DATABASE=:memory:` (bawaan Laravel 11/12; hapus tanda komentar jika perlu). **Screenshot** hasil hijau untuk laporan.

## Tahap 4 — Postman (CPMKI d)

1. Postman → Import → `postman_collection.json` (taruh di akar repo).
2. Ubah password di request **Login** sesuai `ADMIN_PASSWORD`.
3. Jalankan Login dulu: skrip test otomatis menyimpan token ke variabel `{{token}}`.
4. Jalankan semua request; screenshot tiap hasil (201, 422, 401 wajib).
5. Setelah mengubah sesuatu, ekspor ulang: Collection → Export → v2.1 → timpa file di repo.

## Tahap 5 — Frontend Vue (CPMK1)

```bash
cd ..                            # kembali ke akar repo
npm create vite@latest frontend -- --template vue
cd frontend
npm install
npm install axios
```

Hapus `src/components/HelloWorld.vue` dan `src/assets` bawaan jika tidak dipakai. Salin dari zip ke `frontend/`: `src/api.js`, `src/main.js`, `src/style.css`, `src/App.vue`, `src/components/LoginForm.vue`, `BookManager.vue`, `.env.example`.

Buat `frontend/.env` (tidak di-commit; sudah diabaikan oleh `.gitignore` bawaan Vite):

```
VITE_API_URL=http://localhost:8000/api
```

Uji lokal: terminal 1 `php artisan serve` (di `backend/`), terminal 2 `npm run dev` → buka alamat Vite → login → tambah/ubah/hapus buku.

Poin penilaian: token disimpan di `localStorage` dan dikirim lewat interceptor Axios (`src/api.js`); alamat API dari `import.meta.env.VITE_API_URL`, tidak ditulis di komponen.

## Tahap 6 — Commit awal dan setup GitHub (CPMK2 a–b)

Salin ke akar repo: `.gitignore`, `.env.example`, `postman_collection.json`, `.github/workflows/ci.yml`.

**Untuk commit awal, hapus job `deploy`** dari `ci.yml` (semua baris mulai dari komentar `# ===== JOB DEPLOY`). Job itu ditambahkan lagi di Tahap 10.

```bash
cd uts-p1-NIM
git add .
git commit -m "chore: inisialisasi backend laravel dan frontend vue"
```

Di GitHub: buat repository **public** `uts-p1-NIM` (di org KEPL2026 atau akun pribadi), tanpa README/gitignore (repo kosong).

```bash
git remote add origin https://github.com/<owner>/uts-p1-NIM.git
git push -u origin main
git checkout -b dev && git push -u origin dev
```

Buka tab **Actions**: workflow CI harus berjalan dan **hijau** (dua job: `backend-test`, `frontend-build`). Jika merah, lihat Troubleshooting.

> Push langsung ke `main` dan `dev` hanya dilakukan pada langkah ini, sebelum branch protection dipasang. Setelah itu semua lewat Pull Request.

**Pasang branch protection** (baru bisa memilih status check setelah workflow pernah berjalan):
Settings → Branches → Add branch protection rule (atau Settings → Rules → Rulesets), untuk `main` **dan** `dev`:
- Require a pull request before merging
- Require status checks to pass before merging → pilih `backend-test` dan `frontend-build`
- (opsional) Do not allow bypassing the above settings

**Screenshot** halaman pengaturan kedua branch untuk laporan.

## Tahap 7 — Branch fitur dan Pull Request

Alur: `feature/*` → PR → `dev` → PR → `main`.

```bash
git checkout dev
git checkout -b feature/docker
# kerjakan Tahap 8 dan 9 di branch ini
git add . && git commit -m "feat: tambah dockerfile dan docker compose"
git push -u origin feature/docker
```

Di GitHub: buka PR `feature/docker` → `dev`, tunggu check hijau, merge. Lanjut PR `dev` → `main`.
Screenshot yang dibutuhkan: halaman PR dengan check hijau, dan fakta bahwa tombol push langsung ditolak/terproteksi.

## Tahap 8 — Dockerfile (CPMK4–5)

Salin dari zip: `backend/Dockerfile`, `backend/Caddyfile`, `backend/docker-entrypoint.sh`, `backend/.dockerignore`, `frontend/Dockerfile`, `frontend/nginx.conf`, `frontend/.dockerignore`.

Penjelasan untuk laporan:
- **Backend**: base image `dunglas/frankenphp:1.4-php8.3` (versi dipin). `composer.json` + `composer.lock` disalin dan di-install **sebelum** `COPY . .` agar cache lapisan bekerja. Tidak ada secret di Dockerfile; `APP_KEY` dan password datang dari environment saat container jalan. Entrypoint menjalankan `migrate` dan `db:seed`.
- **Frontend**: multi-stage. Tahap 1 `node:22-alpine` menjalankan `npm ci` + `npm run build`; tahap 2 `nginx:1.27-alpine` hanya menyalin folder `dist`, jadi **tidak ada `node_modules`** di image akhir. Buktikan: `docker run --rm --entrypoint sh <image> -c "ls /usr/share/nginx/html; ls /node_modules"`.
- `.dockerignore` di masing-masing folder.

Pastikan `frontend/package-lock.json` dan `backend/composer.lock` ada dan **di-commit** (Dockerfile dan `npm ci` membutuhkannya).

> Verifikasi tag base image sebelum dipakai: buka halaman tags Docker Hub untuk `dunglas/frankenphp`, `mysql`, `nginx`, `node`. Jika tag berbeda, ganti dengan versi spesifik yang ada (jangan `latest`).

## Tahap 9 — Docker Compose (CPMK6–7)

Salin `docker-compose.yml` dan `.env.example` ke akar repo, lalu:

```bash
cp .env.example .env
cd backend && php artisan key:generate --show   # salin hasilnya (base64:...) ke APP_KEY di ../.env
cd ..
# isi DB_PASSWORD, DB_ROOT_PASSWORD, ADMIN_PASSWORD di .env
docker compose up -d --build
docker compose ps                                # screenshot untuk laporan
```

Buka `http://localhost:8080`, login dengan `ADMIN_EMAIL`/`ADMIN_PASSWORD`, uji tambah/ubah/hapus.

**Bagaimana tersambung** (untuk laporan):
- Browser → Nginx di container `frontend` (port 8080). Permintaan `/api/...` diteruskan Nginx ke `http://backend:80`.
- Backend memakai `DB_HOST=db` (nama service) ke MySQL.
- Port 8000 juga dibuka agar Postman bisa menguji backend langsung.

**Uji persistensi (CPMK7 d):**

```bash
# tambah beberapa buku lewat halaman web, lalu:
docker compose down
docker compose up -d
# data masih ada -> named volume db_data bekerja
```

Jangan pakai `down -v`, karena itu menghapus volume.

Alur data untuk laporan: *Vue (Axios + header Bearer) → Nginx `/api` → FrankenPHP/Laravel (Sanctum memeriksa token, controller memvalidasi) → Eloquent → MySQL (volume `db_data`) → respons JSON kembali ke Vue.*

## Tahap 10 — Continuous Deployment (CPMK3)

1. GitHub → Settings → Secrets and variables → Actions → **New repository secret**, buat:
   - `APP_KEY` (isi dengan `base64:...` dari `key:generate --show`)
   - `DB_PASSWORD`
2. Branch baru `feature/cd` dari `dev`, tambahkan kembali job `deploy` di `ci.yml` (versi lengkap ada di zip).
3. PR ke `dev` (CI hijau, **deploy tidak jalan**; screenshot daftar job pada run ini) → merge → PR `dev` ke `main` → merge.
4. Merge ke `main` memicu push ke `main`, jadi job `deploy` berjalan: smoke test compose, login ke ghcr.io, build dan push dua image bertag `${{ github.sha }}`.
5. Bukti untuk laporan: (a) run di `main` dengan `deploy` hijau, (b) run dari PR/`dev`/branch fitur tanpa deploy, (c) halaman Packages (profil/organisasi → Packages, atau sisi kanan halaman repo) berisi `...-backend` dan `...-frontend` dengan tag commit SHA.

Catatan: nama image otomatis dijadikan huruf kecil oleh langkah "Siapkan nama image" karena `KEPL2026` berhuruf besar.

## Tahap 11 — Bukti nilai tambah FrankenPHP

```bash
docker compose logs backend          # tampil access log Caddy/FrankenPHP tiap request
docker compose exec backend frankenphp version
curl -I http://localhost:8000/up     # header "Server: Caddy" menandakan FrankenPHP
```

Lakukan login dari halaman web, lalu screenshot `docker compose logs backend` yang memperlihatkan baris request `POST /api/login`.

## Tahap 12 — Laporan PDF

Isi minimal (susun di Word/Docs lalu ekspor PDF), satu bagian per soal dengan langkah + screenshot:
1. Tautan repository GitHub dan tautan tab Actions
2. Backend: `php artisan test` hijau, Postman (201/422/401), tautan/lokasi Postman Collection
3. Frontend: login, tampil, tambah, ubah, hapus; kode `api.js` dan `.env`
4. GitHub: daftar branch, PR (feature→dev, dev→main), screenshot branch protection `main` dan `dev`, Actions hijau
5. CD: bukti a–d di Tahap 10
6. Dockerfile: penjelasan cache lapisan, multi-stage, bukti tanpa `node_modules`
7. Compose: `docker compose ps`, uji persistensi, penjelasan alur data
8. FrankenPHP: log container
9. **Kendala dan solusi** (catat dari Troubleshooting di bawah sesuai yang kamu alami sendiri)

**Tips:** ambil screenshot saat mengerjakan tiap tahap. Run Actions dan halaman Packages bisa berubah atau tertimpa.

---

## Troubleshooting

| Gejala | Penyebab / solusi |
|---|---|
| 401 berubah jadi redirect/HTML | Header `Accept: application/json` tidak ada. Middleware `ForceJsonResponse` + `shouldRenderJsonWhen` di `bootstrap/app.php` mengatasinya |
| `php artisan test` gagal di CI: "could not find driver" | Tambahkan `pdo_sqlite, sqlite3` di `extensions` setup-php (sudah ada di zip) |
| CI gagal `Please provide a valid cache path` / `npm ci` error | `package-lock.json` belum di-commit |
| Status check tidak muncul di branch protection | Workflow belum pernah berjalan. Push dulu, tunggu selesai, baru pasang |
| Deploy gagal `denied: permission_denied` / 403 ke ghcr.io | Pastikan `permissions: packages: write` pada job; di org cek Settings → Actions → General → Workflow permissions |
| `invalid reference format` saat build image | Nama image harus huruf kecil (sudah ditangani dengan `${GITHUB_REPOSITORY,,}`) |
| Backend container restart terus | Lihat `docker compose logs backend`. Biasanya `APP_KEY` kosong/salah atau DB belum siap (entrypoint menunggu dan mengulang migrate) |
| `SQLSTATE[HY000] [2002] Connection refused` | `DB_HOST` harus `db`, bukan `localhost`/`127.0.0.1` |
| Frontend: CORS error | Dengan Compose, request lewat `/api` pada origin yang sama sehingga tidak ada CORS. Hanya terjadi saat `npm run dev` ke port 8000 (Laravel biasanya mengizinkan `api/*` secara default) |
| Frontend di Docker memanggil `localhost:8000` | `VITE_API_URL` dibaca saat **build**; set lewat `build.args` lalu `docker compose up -d --build` ulang |
| Port 8000/8080/3306 sudah dipakai | Ubah sisi kiri pemetaan port, misal `"8001:80"` |
| Tag image `dunglas/frankenphp:1.4-php8.3` tidak ditemukan | Pilih tag spesifik lain yang ada di Docker Hub (tetap bukan `latest`) |
| Fallback jika FrankenPHP bermasalah | Ganti sementara `CMD` dengan `["php","artisan","serve","--host=0.0.0.0","--port=80"]` pada base `php:8.3-cli`. Nilai tambah tidak didapat, tapi nilai inti aman |
