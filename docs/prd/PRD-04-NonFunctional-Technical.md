# PRD — SPK Pemilihan Lokasi Usaha (AHP + Web-GIS)
## Dokumen 4/4: Non-Functional & Technical Spec

---

## 1. Arsitektur Sistem

Client-Server berbasis web, tiga komponen utama:

```
┌─────────────────────┐     JSON/GeoJSON     ┌──────────────────────┐     SQL      ┌─────────────┐
│  Client (Browser)   │ ◄──────────────────► │  Application Server  │ ◄──────────► │   MySQL     │
│  Leaflet.js + UI     │                       │  Laravel 12 (PHP)    │               │  Database   │
└─────────────────────┘                       └──────────────────────┘               └─────────────┘
```

- **Database Server (MySQL):** menyimpan data pengguna/sesi, atribut kriteria, dan koordinat spasial (lat/long sebagai DOUBLE untuk presisi).
- **Application Server (Laravel 12):** mengendalikan logika bisnis, keamanan, dan mesin perhitungan AHP (lihat PRD-03).
- **Client (Leaflet.js):** merender peta interaktif, overlay GeoJSON batas kelurahan, dan marker hasil ranking di browser.

## 2. Tech Stack

| Kategori | Pilihan | Catatan |
|---|---|---|
| Bahasa & Framework | PHP, Laravel 12 | Pola MVC, ORM Eloquent |
| Database | MySQL | Relasional, tabel kriteria/alternatif/nilai |
| Peta | Leaflet.js + OpenStreetMap basemap | Ringan, mobile-friendly, mendukung buffer zone via plugin |
| Editor | Visual Studio Code | |
| Local Server | Laragon | |
| OS Pengembangan | Windows 10 / CachyOS Linux | |

## 3. Kontrak API (Usulan)

Belum ada spesifikasi endpoint eksplisit di proposal — berikut usulan kontrak REST minimal yang memetakan langsung ke modul di PRD-02, untuk didiskusikan dan disesuaikan saat tahap Design:

| Method | Endpoint | Modul Terkait | Deskripsi |
|---|---|---|---|
| GET | `/api/kriteria` | M3 | Daftar 4 kriteria + deskripsi |
| GET | `/api/alternatif-lokasi?jenis_usaha=` | M1, M5 | Daftar alternatif lokasi + atribut sesuai jenis usaha |
| POST | `/api/sesi/matriks` | M3 | Simpan input matriks perbandingan berpasangan pengguna |
| POST | `/api/sesi/hitung` | M4 | Trigger perhitungan bobot + CR; mengembalikan error jika CR ≥ 0,1 |
| GET | `/api/sesi/{id}/hasil` | M5 | Ranking lokasi + skor akhir untuk sesi tersebut |
| GET | `/api/lokasi/{id}/buffer?radius=` | M6 | Jumlah & daftar kompetitor dalam radius tertentu |
| POST | `/api/sesi/{id}/komparasi` | M7 | Kirim 1–4 lokasi pilihan pengguna, terima perbandingan skor vs rekomendasi #1 |
| GET/POST/PUT/DELETE | `/api/admin/alternatif-lokasi` | M1 | CRUD data master (butuh autentikasi Admin) |
| GET/POST/PUT/DELETE | `/api/admin/kelurahan` | M1 | CRUD data kelurahan + GeoJSON boundary |

## 4. Kebutuhan Non-Fungsional

| Aspek | Kebutuhan |
|---|---|
| **Akurasi** | Perhitungan AHP backend harus 100% identik dengan perhitungan manual Excel untuk data uji yang sama (lihat golden test case di PRD-03 §3.3) |
| **Performa** | Peta dan hasil ranking tampil tanpa reload penuh saat parameter (radius buffer, jenis usaha) diubah |
| **Keamanan** | Endpoint `/api/admin/*` wajib autentikasi; validasi input mencegah SQL injection di form matriks & data lokasi |
| **Kompatibilitas** | Antarmuka responsif — bisa dipakai di browser desktop maupun mobile, karena UAT melibatkan pelaku usaha & masyarakat langsung di lapangan |
| **Presisi Data Spasial** | Kolom latitude/longitude memakai tipe DOUBLE/FLOAT, bukan pembulatan yang bisa menggeser posisi marker |
| **Auditability** | Nilai CR dan bobot kriteria tiap sesi disimpan (bukan cuma hasil akhir), agar bisa ditelusuri saat pengujian validasi |

## 5. Kriteria Pengujian & Verifikasi

Mengikuti Bab 3.4.4 proposal, tiga metode pengujian wajib dilalui sebelum sistem dinyatakan selesai:

### 5.1 Black Box Testing (Fungsionalitas)
Target skenario uji minimal:
- Fungsi manajemen data alternatif lokasi Laundry & Kafe oleh Admin
- Fungsi form pengisian matriks perbandingan kriteria AHP
- Fungsi akurasi tombol pemanggil visualisasi peta interaktif Leaflet.js
- Fungsi responsivitas buffer zone dan popup marker

### 5.2 Validasi Matematis AHP (Cross-Check)
- Input sampel data kriteria & alternatif yang sama ke sistem web dan ke Microsoft Excel secara manual.
- Bandingkan output: bobot kriteria, CI, CR, hingga skor akhir ranking.
- **Lolos jika:** hasil Laravel identik 100% secara presisi dengan hasil manual Excel.

### 5.3 User Acceptance Testing (UAT)
- Melibatkan pelaku usaha laundry/kafe dan masyarakat Kecamatan Sumbersari sebagai end-user.
- Kuesioner skala Likert, mengukur:
  - Kemudahan pemahaman informasi peta
  - Kecepatan pemrosesan keputusan AHP
  - Kegunaan praktis sistem dalam membantu mencari lokasi usaha strategis

## 6. Milestone (mengikuti Waterfall, ±6 bulan)

| Tahap | Perkiraan Periode | Fokus |
|---|---|---|
| Requirements | Desember 2026 | Analisis kebutuhan data, fungsional, non-fungsional |
| Design | Januari 2027 | Arsitektur, ERD, use case/activity diagram, mockup interface |
| Implementation | Februari–Maret 2027 | Backend Laravel + mesin AHP, frontend Leaflet.js |
| Verification | April 2027 | Black box, validasi matematis, UAT |
| Maintenance | Mei 2027 | Perbaikan berdasarkan hasil UAT |

*Catatan: pemetaan bulan-ke-tahap ini diperkirakan dari struktur Tabel 3.2 di proposal (kolom checklist bulan tidak terbaca penuh saat ekstraksi dokumen) — konfirmasikan tanggal pastinya dengan jadwal bimbingan aktualmu.*
