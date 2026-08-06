# AGENTS.md

Instruksi ini untuk AI coding agent (Claude Code, Cursor, dsb.) yang bekerja di repo ini. Baca dokumen di `/docs` (PRD-01 s/d PRD-04, design.md) sebelum mengimplementasikan fitur apa pun — dokumen itu adalah sumber kebenaran (source of truth), bukan file ini. File ini hanya menjelaskan *cara kerja* di repo, bukan *apa* yang dibangun.

---

## Ringkasan Proyek

SPK (Sistem Pendukung Keputusan) berbasis Web-GIS yang mengintegrasikan metode AHP untuk membantu pemilihan lokasi strategis usaha Laundry dan Kafe di Kecamatan Sumbersari, Kabupaten Jember. Skripsi Teknik Informatika, POLIJE.

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP, Laravel 12, pola MVC |
| Database | MySQL |
| Peta | Leaflet.js + OpenStreetMap basemap |
| ORM | Eloquent |
| Environment lokal | Laragon |

## Struktur Repo (target)

Proyek belum di-scaffold. Struktur yang diharapkan:

```
app/
  Http/Controllers/
  Http/Requests/           # validasi input (form matriks AHP, data lokasi)
  Models/
  Services/AHP/            # SEMUA logika perhitungan AHP hidup di sini, bukan di controller
    AhpMatrixService.php
    ConsistencyValidator.php
    RankingCalculator.php
database/
  migrations/
  seeders/                  # data kriteria tetap (4 kriteria), data uji dari PRD-03 §3.3
resources/
  js/map/                   # inisialisasi Leaflet, layer control, buffer zone
  css/tokens.css            # variabel warna & tipografi — HARUS sinkron dengan docs/design.md
routes/
  api.php                   # ikuti kontrak di docs/PRD-04 §3
tests/
  Unit/AhpEngineTest.php    # wajib pakai golden test case dari PRD-03 §3.3
  Feature/                  # skenario black box dari docs/TEST-PLAN.md
docs/
  PRD-01-Overview.md
  PRD-02-Functional-Requirements.md
  PRD-03-Data-Model-AHP-Spec.md
  PRD-04-NonFunctional-Technical.md
  TEST-PLAN.md
  design.md
```

## Setup & Perintah

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve          # backend, default :8000
npm run dev                # asset build (Vite)
php artisan test           # jalankan seluruh test suite
```

Sebelum membuka PR/menyelesaikan tugas: jalankan `php artisan test` dan pastikan `AhpEngineTest` lulus — ini test yang paling kritis di proyek ini.

## Aturan Domain — Tidak Bisa Dinegosiasikan

Ini bukan preferensi gaya, ini aturan bisnis dari metodologi skripsi (lihat PRD-03). Agent tidak boleh menyimpang dari ini meski diminta "supaya lebih simpel":

1. **Tepat 4 kriteria tetap**: Harga Sewa Lahan/Bangunan, Kepadatan Penduduk, Kedekatan dengan Kompetitor Sejenis, Tingkat Keamanan Lingkungan. Jangan menambah/mengurangi kriteria tanpa mengubah PRD-03 terlebih dahulu.
2. **Threshold CR = 0,1 bersifat keras**. Jika `CR >= 0.1`, sistem WAJIB menolak lanjut dan meminta input ulang. Jangan pernah membuat "mode skip validasi" bahkan untuk keperluan demo/testing di production path.
3. **Bobot kriteria dihitung terpisah per jenis usaha** (Laundry vs Kafe). Jangan pernah men-share atau mencampur bobot antar jenis usaha — keduanya independen secara matematis meski memakai 4 kriteria yang sama.
4. **RI (Random Index) tetap**, ambil dari tabel di PRD-03 §3.2 (n=4 → RI=0,90). Jangan hardcode nilai RI yang berbeda atau menghitungnya secara dinamis.
5. **Wilayah dibatasi Kecamatan Sumbersari**. Validasi koordinat baru terhadap batas wilayah ini sebelum disimpan sebagai `alternatif_lokasi`.
6. **Arah normalisasi kriteria harus benar**: harga sewa & jumlah kompetitor = *lebih rendah lebih baik*; kepadatan penduduk & skor keamanan = *lebih tinggi lebih baik*. Salah arah di sini akan membalik seluruh hasil ranking tanpa error yang terlihat — ini bug paling berbahaya di sistem ini karena silent.

## Konvensi Kode

- PHP: ikuti PSR-12. Model StudlyCase singular (`AlternatifLokasi`), tabel snake_case plural (`alternatif_lokasi` — cek konsistensi penamaan Laravel default).
- Logika perhitungan AHP HARUS ada di `app/Services/AHP/`, bukan di controller atau model. Controller hanya orkestrasi request → service → response.
- Validasi input pakai Form Request class (`App\Http\Requests`), bukan validasi manual di controller.
- Frontend: warna dan font WAJIB memakai token dari `docs/design.md` (mis. `--tinta`, `--cyan-cetak`, Space Grotesk/IBM Plex). Jangan menambah warna ad hoc di luar palet yang sudah didefinisikan.
- Endpoint API mengikuti kontrak di `docs/PRD-04-NonFunctional-Technical.md` §3. Jika perlu endpoint baru di luar daftar itu, update dokumen itu dulu, baru implementasi.

## Testing Wajib

- Setiap perubahan pada `app/Services/AHP/` harus lolos golden test case di `docs/PRD-03-Data-Model-AHP-Spec.md` §3.3 (kasus Laundry: bobot Harga Sewa=0,1608; Kepadatan=0,4661; Kompetitor=0,0958; Keamanan=0,2773; CR=0,0048).
- Skenario black box mengikuti `docs/TEST-PLAN.md` — jangan menandai fitur "selesai" tanpa skenario itu lulus.
- Untuk fitur peta/buffer zone, verifikasi manual di browser tetap wajib karena rendering Leaflet.js sulit di-assert lewat unit test murni.

## Yang TIDAK Boleh Dilakukan Agent

- Jangan commit `.env` atau kredensial database.
- Jangan menonaktifkan/melewati validasi Consistency Ratio untuk "mempermudah testing", bahkan sementara.
- Jangan mengubah ruang lingkup (jenis usaha, wilayah) tanpa flag eksplisit dari pengguna DAN update `docs/PRD-01-Overview.md`.
- Jangan menambahkan dependency besar (library AHP pihak ketiga, dsb.) tanpa konfirmasi — proposal skripsi mensyaratkan implementasi algoritma ditulis sendiri di PHP (Bab 3.4.3) sebagai bagian dari kontribusi ilmiah, bukan memanggil library eksternal.
