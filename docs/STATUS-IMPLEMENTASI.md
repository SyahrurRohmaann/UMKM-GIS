# Status Implementasi vs PRD

Ringkasan keterlacakan (traceability) antara modul di dokumen kebutuhan
(`docs/prd/`) dengan kondisi kode saat ini. Diperbarui pada iterasi perbaikan
skoring + M1 + M6/M7 + pembersihan kode.

| Modul | Deskripsi | Status | Catatan implementasi |
|---|---|---|---|
| **M1** | Manajemen data master (alternatif lokasi, kelurahan) | ✅ Selesai | CRUD kelurahan + kepadatan penduduk terisi; validasi spasial `WithinSumbersari` menolak koordinat di luar batas kecamatan pada form alternatif. |
| **M2** | Pemilihan jenis usaha | ✅ Selesai | Dropdown jenis usaha di peta men-trigger set lokasi & kompetitor. |
| **M3'** | Input matriks AHP oleh admin (expert-based) | ✅ Selesai | Form matriks Saaty 1–9 di panel admin (`/admin/ahp-config`); bobot dari wawancara pakar, bukan input pengguna publik. |
| **M4** | Perhitungan & validasi konsistensi (CR) | ✅ Selesai | `AhpService` menghitung eigenvector, λmax, CI, CR; CR ≥ 0,1 ditolak. Uji golden case (PRD-03 §3.3) lolos. |
| **M5** | Visualisasi peta rekomendasi | ✅ Selesai | Leaflet + marker berperingkat; skoring SAW **normalisasi min-max** seragam di kedua endpoint. |
| **M6** | Buffer zone (analisis spasial) | ✅ Selesai | Radius **dapat dikonfigurasi** pengguna (tidak lagi 500m mati); jumlah kompetitor dalam radius via endpoint Haversine. |
| **M7** | Komparasi lokasi pribadi | ✅ Selesai | Pilihan lokasi user **disimpan** ke tabel `lokasi_pilihan_user` (tertaut `sesi_perhitungan`) beserta skor terhitung. |

## Keputusan metode penting

1. **Skoring SAW pakai normalisasi min-max** (bukan rasio), seragam di
   `AhpService::hitungSkorAkhir` dan `ScoringService::calculateFinalScores`.
   Memperbaiki bug ranking saat nilai kriteria identik / kompetitor = 0 di semua
   lokasi. Lihat PRD-03 §3.4.
2. **AHP berbasis wawancara pakar** (expert-based). Bobot diinput admin dari
   matriks konsensus hasil wawancara, bukan diisi pengguna publik. Lihat
   PRD-02 M3'.
3. **Golden test** — selisih CR mesin (presisi penuh) vs Excel (pembulatan dini)
   wajar dan tidak mengubah kesimpulan; bobot identik hingga 3 desimal. Lihat
   PRD-03 §3.3.

## Verifikasi

Seluruh perilaku inti diuji otomatis (`vendor/bin/phpunit`) memakai SQLite
in-memory: mesin AHP (golden case), skoring SAW (termasuk regresi), validasi
spasial, CRUD kelurahan, endpoint rekomendasi & persistensi pilihan user.

## Sisa peningkatan (opsional / di luar Must-Have)

- Fitur agregasi matriks per-responden di dalam sistem (saat ini konsensus
  dihitung di luar & didokumentasikan sebagai lampiran skripsi).
- Halaman bantuan/onboarding pengguna awam.
