# PRD — SPK Pemilihan Lokasi Usaha (AHP + Web-GIS)
## Dokumen 2/4: Functional Requirements

Lihat PRD-01 untuk konteks & scope. Modul di bawah mengikuti alur nyata pengguna: pilih jenis usaha → isi preferensi AHP → sistem hitung & validasi → lihat peta → analisis spasial → bandingkan lokasi pribadi.

---

## Alur Pengguna Ringkas

```
[Pilih Jenis Usaha: Laundry / Kafe]
            │
            ▼
[Isi Matriks Perbandingan Berpasangan (AHP, skala 1-9)]
            │
            ▼
      ┌─────────────┐
      │ Hitung CR   │──── CR ≥ 0,1 ──► [Tampilkan pesan error, minta isi ulang]
      └─────────────┘
            │ CR < 0,1
            ▼
[Sistem hitung skor akhir & ranking tiap alternatif lokasi]
            │
            ▼
[Tampilkan peta interaktif: marker berwarna/berurutan sesuai skor]
            │
            ├──► [Aktifkan buffer zone di titik tertentu → lihat kompetitor sekitar]
            │
            └──► [Pilih 1-4 lokasi pribadi → bandingkan skor vs rekomendasi sistem]
```

---

## M1. Manajemen Data Master (Admin)

**User Story:** Sebagai Admin, saya ingin menambah, mengubah, dan menghapus data alternatif lokasi beserta atributnya, sehingga data yang dipakai sistem untuk perhitungan AHP selalu akurat dan terkini.

**Kriteria Penerimaan:**
- [ ] Admin dapat CRUD data alternatif lokasi: nama/label lokasi, koordinat (latitude, longitude), jenis usaha terkait (Laundry/Kafe/keduanya).
- [ ] Admin dapat CRUD atribut kriteria per lokasi: harga sewa (Rp/tahun), skor keamanan (1–4), jumlah kompetitor dalam radius tertentu.
- [ ] Admin dapat CRUD data kepadatan penduduk per kelurahan (sumber: BPS) yang dipakai lintas-lokasi dalam kelurahan yang sama.
- [ ] Admin dapat mengelola data batas wilayah kelurahan (GeoJSON) yang ditampilkan sebagai overlay peta.
- [ ] Sistem menolak simpan data koordinat yang berada di luar batas Kecamatan Sumbersari (validasi spasial dasar).
- [ ] Perubahan data oleh Admin langsung memengaruhi hasil perhitungan berikutnya (tidak perlu deploy ulang).

**Prioritas:** Must Have — tanpa modul ini tidak ada data untuk dihitung.

---

## M2. Pemilihan Jenis Usaha (Action Trigger)

**User Story:** Sebagai Pelaku Usaha, saya ingin memilih jenis usaha (Laundry atau Kafe) di awal, sehingga seluruh kriteria, bobot, dan data kompetitor yang saya lihat relevan dengan usaha saya.

**Kriteria Penerimaan:**
- [ ] Halaman awal menampilkan pilihan jenis usaha sebagai langkah wajib pertama sebelum fitur lain aktif.
- [ ] Pilihan jenis usaha menentukan: set alternatif lokasi yang ditampilkan, definisi "kompetitor sejenis" yang dihitung, dan bobot kriteria yang dipakai pada perhitungan AHP.
- [ ] Pengguna dapat mengganti jenis usaha kapan saja, dan sistem me-reset progres input AHP terkait (dengan konfirmasi agar tidak hilang tanpa sengaja).

**Prioritas:** Must Have.

---

## M3. Input Preferensi Kriteria — Matriks Perbandingan Berpasangan AHP

**User Story:** Sebagai Pelaku Usaha, saya ingin membandingkan tingkat kepentingan tiap pasang kriteria menggunakan skala 1–9, sehingga sistem tahu prioritas saya secara personal, bukan asumsi generik.

**Kriteria Penerimaan:**
- [ ] Form menampilkan seluruh pasangan kriteria yang perlu dibandingkan (untuk 4 kriteria → 6 pasangan perbandingan unik).
- [ ] Input menggunakan skala Saaty 1–9 beserta nilai kebalikan (1/3, 1/5, dst.) sesuai Tabel Skala Preferensi AHP — lihat PRD-03.
- [ ] Setiap slider/pilihan memakai bahasa yang jelas dari sudut pengguna (nama kriteria asli, bukan "Kriteria A vs Kriteria B").
- [ ] Pengguna bisa mengubah input sebelum submit final.
- [ ] Data hasil perbandingan disimpan terpisah per sesi/pengguna, tertaut ke jenis usaha yang dipilih pada M2.

**Prioritas:** Must Have.

---

## M4. Perhitungan & Validasi Konsistensi AHP

**User Story:** Sebagai Pelaku Usaha, saya ingin sistem memberi tahu jika penilaian saya tidak konsisten, sehingga saya bisa memperbaikinya sebelum melihat hasil yang bias.

**Kriteria Penerimaan:**
- [ ] Sistem menghitung normalisasi matriks, eigenvector (bobot prioritas), λmax, CI, dan CR secara otomatis di backend (lihat rumus & contoh di PRD-03).
- [ ] Jika **CR ≥ 0,1**: sistem menampilkan pesan error yang menjelaskan (bukan hanya angka CR mentah) dan mengarahkan pengguna meninjau ulang pasangan perbandingan yang paling mencolok, lalu memblokir lanjut ke hasil.
- [ ] Jika **CR < 0,1**: sistem lanjut menghitung skor akhir tiap alternatif lokasi dan menyimpan bobot kriteria hasil sesi ini.
- [ ] Hasil perhitungan backend (bobot & CR) harus 100% identik dengan perhitungan manual di Excel untuk data input yang sama (kriteria uji, lihat PRD-04).

**Prioritas:** Must Have — ini jantung validitas ilmiah sistem.

---

## M5. Visualisasi Peta Interaktif & Perankingan Lokasi

**User Story:** Sebagai Pelaku Usaha, saya ingin melihat hasil rekomendasi lokasi langsung di atas peta, sehingga saya paham posisi geografisnya, bukan cuma tabel angka.

**Kriteria Penerimaan:**
- [ ] Peta (Leaflet.js) menampilkan basemap dengan overlay batas kelurahan Kecamatan Sumbersari dari data GeoJSON.
- [ ] Tiap alternatif lokasi ditampilkan sebagai marker; warna/ukuran/urutan marker mencerminkan skor akhir AHP (rekomendasi tertinggi paling menonjol).
- [ ] Marker dibedakan visual antara jenis usaha Laundry dan Kafe (ikon atau warna berbeda) jika keduanya tampil bersamaan.
- [ ] Klik/tap marker menampilkan popup info: nama lokasi, skor akhir, dan rincian nilai per kriteria.
- [ ] Peta responsif dan dapat digunakan di perangkat mobile.

**Prioritas:** Must Have.

---

## M6. Analisis Spasial — Buffer Zone & Popup Marker

**User Story:** Sebagai Pelaku Usaha, saya ingin melihat radius jangkauan dari satu titik lokasi, sehingga saya tahu berapa banyak kompetitor sejenis ada di sekitarnya secara real-time.

**Kriteria Penerimaan:**
- [ ] Pengguna dapat mengaktifkan buffer zone (radius, default contoh: 500 meter, dapat dikonfigurasi) dari titik lokasi yang dipilih.
- [ ] Sistem menghitung dan menampilkan jumlah kompetitor sejenis (sesuai jenis usaha aktif) yang berada dalam radius tersebut.
- [ ] Buffer zone digambar sebagai lingkaran di peta dan diperbarui langsung saat radius diubah, tanpa reload halaman.
- [ ] Popup marker menampilkan info ini secara ringkas saat buffer aktif.

**Prioritas:** Should Have — memperkuat nilai analitis tapi sistem tetap berfungsi tanpanya untuk MVP.

---

## M7. Komparasi Lokasi Pilihan Pengguna vs Rekomendasi Sistem

**User Story:** Sebagai Pelaku Usaha, saya sudah punya 1–4 kandidat lokasi sendiri, dan saya ingin tahu seberapa baik pilihan saya dibanding rekomendasi terbaik sistem, sehingga saya bisa memutuskan dengan percaya diri.

**Kriteria Penerimaan:**
- [ ] Pengguna dapat memilih 1 sampai maksimal 4 lokasi (dari daftar alternatif yang ada di sistem, atau menandai titik baru di peta) sebagai "lokasi pilihan saya".
- [ ] Sistem menghitung skor AHP untuk lokasi pilihan pengguna menggunakan bobot kriteria hasil sesi M3–M4 yang sama.
- [ ] Sistem menampilkan perbandingan berdampingan: skor lokasi pilihan pengguna vs skor lokasi rekomendasi terbaik tersimpan (rank #1 sistem), termasuk selisih per kriteria.
- [ ] Perbandingan ditampilkan baik dalam bentuk visual (peta/kartu) maupun ringkasan angka.

**Prioritas:** Must Have — ini menjawab langsung rumusan masalah poin 3.

---

## Ringkasan Prioritas (MoSCoW)

| Modul | Prioritas | Alasan |
|---|---|---|
| M1 Manajemen Data Master | Must | Prasyarat data untuk semua modul lain |
| M2 Pemilihan Jenis Usaha | Must | Trigger yang menentukan seluruh alur berikutnya |
| M3 Input Matriks AHP | Must | Inti metode penelitian |
| M4 Perhitungan & Validasi CR | Must | Menjamin validitas ilmiah hasil |
| M5 Visualisasi Peta | Must | Nilai jual utama Web-GIS |
| M6 Buffer Zone | Should | Nilai tambah analisis spasial |
| M7 Komparasi Lokasi Pengguna | Must | Menjawab rumusan masalah poin 3 |

Modul **M6** boleh dikerjakan belakangan jika waktu 6 bulan mepet — tapi tetap wajib ada di UAT karena disebut eksplisit di kriteria pengujian fungsionalitas (Bab 3.4.4).
