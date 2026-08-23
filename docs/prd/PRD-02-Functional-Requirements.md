# PRD — SPK Pemilihan Lokasi Usaha (AHP + Web-GIS)
## Dokumen 2/4: Functional Requirements

Lihat PRD-01 untuk konteks & scope. Modul di bawah mengikuti alur nyata pengguna: pilih jenis usaha → isi preferensi AHP → sistem hitung & validasi → lihat peta → analisis spasial → bandingkan lokasi pribadi.

---

## Alur Pengguna Ringkas

> **Catatan metode (penting):** Bobot kriteria pada sistem ini **tidak** diisi oleh
> pelaku usaha (pengguna publik), melainkan diperoleh melalui **wawancara pakar/
> pemangku kepentingan** terkait, lalu diagregasi menjadi satu matriks perbandingan
> konsensus dan diinput oleh **Administrator** melalui modul Konfigurasi AHP
> (lihat M3'). Pendekatan *expert-based AHP* ini dipilih karena penilaian
> perbandingan berpasangan menuntut pemahaman domain yang memadai; validitasnya
> dijaga oleh uji Consistency Ratio (CR < 0,1). Pelaku usaha berinteraksi mulai
> dari pemilihan jenis usaha → melihat hasil ranking di peta → analisis spasial →
> komparasi lokasi pribadi.

```
[ADMIN: Input Matriks Perbandingan hasil wawancara pakar (AHP, skala 1-9)]
            │
            ▼
      ┌─────────────┐
      │ Hitung CR   │──── CR ≥ 0,1 ──► [Tampilkan pesan error, minta isi ulang]
      └─────────────┘
            │ CR < 0,1
            ▼
   [Bobot kriteria tersimpan per jenis usaha]
· · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · ·
[PELAKU USAHA: Pilih Jenis Usaha: Laundry / Kafe]
            │
            ▼
[Sistem hitung skor akhir & ranking tiap alternatif lokasi memakai bobot tersimpan]
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
- [ ] Pilihan jenis usaha menentukan: set alternatif lokasi yang ditampilkan, definisi "kompetitor sejenis" yang dihitung, dan bobot kriteria (hasil wawancara pakar yang tersimpan) yang dipakai pada perhitungan skor.
- [ ] Pengguna dapat mengganti jenis usaha kapan saja, dan sistem me-reset progres analisis terkait.

**Prioritas:** Must Have.

---

## M3'. Input Matriks Perbandingan AHP oleh Admin (Berbasis Wawancara Pakar)

**User Story:** Sebagai Administrator/Peneliti, saya ingin memasukkan matriks perbandingan berpasangan hasil wawancara pakar untuk tiap jenis usaha, sehingga bobot kriteria bersumber dari penilaian ahli yang dapat dipertanggungjawabkan, bukan input pengguna acak.

**Latar keputusan desain:** Penilaian perbandingan berpasangan (skala Saaty 1–9) menuntut pemahaman domain terhadap trade-off antar-kriteria. Dalam penelitian ini bobot diperoleh melalui **wawancara pemangku kepentingan/pakar** terkait tiap jenis usaha; hasilnya diagregasi (mis. rata-rata geometrik antar-responden, dihitung di luar sistem sebagai lampiran) menjadi satu matriks konsensus, lalu diinput admin. Karena itu form matriks berada di **panel admin** (`admin/ahp-config`), bukan di sisi pelaku usaha.

**Kriteria Penerimaan:**
- [ ] Panel admin menampilkan seluruh pasangan kriteria yang perlu dibandingkan (untuk 4 kriteria → 6 pasangan perbandingan unik).
- [ ] Input menggunakan skala Saaty 1–9 beserta nilai kebalikan (1/3, 1/5, dst.) sesuai Tabel Skala Preferensi AHP — lihat PRD-03.
- [ ] Setiap pilihan memakai nama kriteria asli yang jelas (mis. "Sewa vs Kepadatan Penduduk"), bukan "Kriteria A vs Kriteria B".
- [ ] Matriks & bobot hasil disimpan **terpisah per jenis usaha**; menyimpan ulang menimpa konfigurasi jenis usaha tersebut.
- [ ] Admin dapat mengubah nilai perbandingan dan menyimpan ulang kapan saja tanpa deploy ulang.

**Prioritas:** Must Have.

**Jejak bukti wawancara (rekomendasi untuk skripsi):** Sistem menyimpan matriks konsensus final. Matriks per responden dan proses agregasinya didokumentasikan sebagai **lampiran skripsi** (mis. tabel per narasumber + rata-rata geometrik). Bila waktu memungkinkan, penambahan tabel responden + fitur agregasi di sistem menjadi nilai tambah (opsional, Should Have).

---

## M4. Perhitungan & Validasi Konsistensi AHP

**User Story:** Sebagai Administrator/Peneliti, saya ingin sistem memberi tahu jika matriks perbandingan hasil wawancara tidak konsisten, sehingga saya bisa memperbaikinya sebelum bobot dipakai untuk menghitung hasil yang bias.

**Kriteria Penerimaan:**
- [ ] Sistem menghitung normalisasi matriks, eigenvector (bobot prioritas), λmax, CI, dan CR secara otomatis di backend (lihat rumus & contoh di PRD-03).
- [ ] Jika **CR ≥ 0,1**: sistem menolak menyimpan bobot dan menampilkan pesan yang menjelaskan (memuat nilai CR), meminta admin meninjau ulang nilai perbandingan.
- [ ] Jika **CR < 0,1**: sistem menyimpan bobot kriteria untuk jenis usaha terkait, siap dipakai pada perhitungan skor lokasi.
- [ ] Hasil perhitungan backend (bobot & CR) harus konsisten dengan perhitungan manual di Excel untuk data input yang sama (kriteria uji, lihat PRD-04 & catatan golden test di PRD-03 §3.3).

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
| M3' Input Matriks AHP (Admin, wawancara pakar) | Must | Inti metode penelitian |
| M4 Perhitungan & Validasi CR | Must | Menjamin validitas ilmiah hasil |
| M5 Visualisasi Peta | Must | Nilai jual utama Web-GIS |
| M6 Buffer Zone | Should | Nilai tambah analisis spasial |
| M7 Komparasi Lokasi Pengguna | Must | Menjawab rumusan masalah poin 3 |

Modul **M6** boleh dikerjakan belakangan jika waktu 6 bulan mepet — tapi tetap wajib ada di UAT karena disebut eksplisit di kriteria pengujian fungsionalitas (Bab 3.4.4).
