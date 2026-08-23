# SPK Pemilihan Lokasi Usaha (AHP + Web-GIS)

Sistem Pendukung Keputusan (SPK) untuk merekomendasikan lokasi usaha
(**Laundry** / **Kafe**) di Kecamatan Sumbersari, Jember, menggunakan metode
**Analytic Hierarchy Process (AHP)** yang dipadukan dengan **Web-GIS**
(peta interaktif Leaflet).

Aplikasi ini adalah artefak penelitian skripsi. Bobot kriteria diperoleh dari
**wawancara pakar** (expert-based AHP), diinput administrator, divalidasi
Consistency Ratio (CR < 0,1), lalu dipakai untuk menghitung dan memeringkat
alternatif lokasi di atas peta.

---

## Fitur Utama

- **Konfigurasi AHP (Admin):** input matriks perbandingan berpasangan Saaty 1–9
  per jenis usaha, hitung bobot (eigenvector) + validasi CR otomatis.
- **Manajemen data master (Admin):** CRUD alternatif lokasi & kelurahan
  (kepadatan penduduk), dengan **validasi spasial** menolak koordinat di luar
  batas Kecamatan Sumbersari.
- **Peta rekomendasi:** pilih jenis usaha → sistem memeringkat lokasi dengan
  Simple Additive Weighting (SAW) berbobot AHP, ditampilkan sebagai marker
  berperingkat.
- **Analisis spasial (buffer zone):** hitung jumlah kompetitor sejenis dalam
  radius yang dapat diatur.
- **Komparasi lokasi pribadi:** pilih 1–4 titik sendiri dan bandingkan skornya
  dengan rekomendasi sistem.

---

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 13 (PHP 8.3) |
| Basis data | MySQL (produksi) / SQLite in-memory (testing) |
| Frontend | Blade + Bootstrap 5, jQuery |
| Peta | Leaflet.js + basemap CartoDB, overlay GeoJSON |
| Metode | AHP (pembobotan) + SAW normalisasi min-max (skoring) |

---

## Prasyarat

- PHP 8.3 dengan ekstensi: `pdo`, `mbstring`, `openssl`, `tokenizer`, `dom`,
  `xml`, `bcmath`, `fileinfo`, `ctype`, dan driver DB (`pdo_mysql` atau `pdo_sqlite`).
- Composer 2.x
- Node.js 18+ & npm (untuk build aset frontend)
- MySQL 8 (opsional; bisa pakai SQLite untuk pengembangan cepat)

---

## Instalasi

```bash
# 1. Clone
git clone https://github.com/SyahrurRohmaann/UMKM-GIS.git
cd UMKM-GIS

# 2. Dependensi PHP & JS
composer install
npm install

# 3. Konfigurasi environment
cp .env.example .env
php artisan key:generate

# 4. Siapkan database (lihat opsi di bawah), lalu migrasi + seed
php artisan migrate --seed

# 5. Build aset frontend
npm run build        # atau: npm run dev (mode pengembangan)

# 6. Jalankan server
php artisan serve
```

Buka http://localhost:8000 — otomatis diarahkan ke halaman peta.

### Opsi database

**SQLite (paling cepat untuk pengembangan)** — di `.env`:

```env
DB_CONNECTION=sqlite
```

Lalu buat file DB kosong:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

**MySQL** — di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_lokasi
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `spk_lokasi` terlebih dahulu, lalu `php artisan migrate --seed`.

---

## Kredensial Admin

`DatabaseSeeder` membuat satu akun admin:

- **Email:** `admin@example.com`
- **Password:** dibuat acak oleh factory Laravel.

Untuk menetapkan password yang pasti, jalankan setelah seeding:

```bash
php artisan tinker
>>> $u = App\Models\User::where('email','admin@example.com')->first();
>>> $u->password = Illuminate\Support\Facades\Hash::make('password123');
>>> $u->save();
```

Login admin: http://localhost:8000/login → panel di `/admin`.

---

## Data yang Di-seed

- **Kelurahan** — 7 kelurahan Kec. Sumbersari + kepadatan penduduk (sumber BPS).
- **Kriteria** — 4 kriteria AHP: Harga Sewa, Kepadatan Penduduk, Kedekatan
  Kompetitor, Tingkat Keamanan.
- **Jenis Usaha** — Laundry & Kafe.
- **Alternatif Lokasi** — titik kandidat & kompetitor eksisting per jenis usaha.

> Bobot AHP **tidak** di-seed. Admin mengisinya lewat menu **Bobot AHP** setelah
> login (hasil wawancara pakar). Bila belum diisi, endpoint rekomendasi memakai
> bobot fallback untuk MVP.

---

## Menjalankan Test

```bash
php artisan test
# atau langsung:
vendor/bin/phpunit
```

Suite pengujian memakai SQLite in-memory (dikonfigurasi di `phpunit.xml`),
jadi tidak memerlukan server DB. Mencakup:

- **Unit** — mesin AHP (`AhpService`, uji golden case PRD-03), skoring SAW
  (`ScoringService`, termasuk regresi bug normalisasi), validasi spasial
  (`WithinSumbersari`).
- **Feature** — endpoint API AHP & rekomendasi, CRUD kelurahan.

---

## Alur Metode (Ringkas)

```
[ADMIN] Wawancara pakar → matriks konsensus → input di /admin/ahp-config
        → hitung bobot (eigenvector) + validasi CR < 0,1 → simpan per jenis usaha
                                   │
[PELAKU USAHA] Pilih jenis usaha  ▼
        → SAW (normalisasi min-max × bobot AHP) → ranking lokasi di peta
        → buffer zone (kompetitor dalam radius) → komparasi lokasi pribadi
```

Detail spesifikasi ada di `docs/prd/` (PRD-01 s.d. PRD-04) dan `docs/alur-sistem.md`.

---

## Struktur Direktori Penting

```
app/
  Http/Controllers/Admin/     # panel admin: AHP config, alternatif, kelurahan, dashboard
  Http/Controllers/Api/       # endpoint peta: rekomendasi, kalkulasi, geojson, lokasi
  Http/Controllers/Web/       # MapController (halaman peta publik)
  Services/AhpService.php     # mesin AHP: bobot, λmax, CI, CR
  Services/ScoringService.php # SAW normalisasi min-max
  Rules/WithinSumbersari.php  # validasi spasial (point-in-polygon)
docs/prd/                     # dokumen kebutuhan (PRD 1–4)
public/assets/geojson/        # batas wilayah Sumbersari
resources/views/              # blade (map, admin, layouts)
```

---

## Lisensi

Kode aplikasi untuk keperluan penelitian akademik. Framework Laravel berlisensi MIT.
