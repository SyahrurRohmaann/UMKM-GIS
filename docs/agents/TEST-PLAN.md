# Test Plan — SPK Pemilihan Lokasi Usaha (AHP + Web-GIS)

Mengoperasionalkan Bab 3.4.4 (*Verification*) proposal menjadi skenario yang bisa langsung dieksekusi. Tiga metode pengujian: Black Box, Validasi Matematis AHP, dan UAT.

---

## 1. Black Box Testing (Fungsionalitas)

Format: ID, Modul, Skenario, Langkah Uji, Hasil Diharapkan. Referensi modul mengikuti `PRD-02-Functional-Requirements.md`.

| ID | Modul | Skenario | Langkah Uji | Hasil Diharapkan |
|---|---|---|---|---|
| BB-01 | M1 Manajemen Data | Admin menambah alternatif lokasi baru | Login Admin → isi form lokasi (nama, koordinat, harga sewa, skor keamanan) → simpan | Lokasi baru muncul di daftar & langsung tersedia untuk perhitungan berikutnya |
| BB-02 | M1 Manajemen Data | Admin input koordinat di luar Kec. Sumbersari | Isi lat/long yang berada di luar batas wilayah → simpan | Sistem menolak simpan, tampilkan pesan validasi wilayah |
| BB-03 | M2 Pemilihan Jenis Usaha | Pengguna memilih jenis usaha Kafe | Buka halaman awal → pilih "Kafe" | Sistem memuat set kriteria & kompetitor khusus Kafe, bukan Laundry |
| BB-04 | M3 Form Matriks AHP | Pengguna mengisi 6 pasangan perbandingan kriteria | Isi seluruh slider skala 1–9 untuk 4 kriteria → submit | Semua nilai tersimpan sesuai input, termasuk nilai kebalikan otomatis terhitung |
| BB-05 | M4 Validasi CR | Pengguna mengisi matriks yang tidak konsisten (CR ≥ 0,1) | Isi kombinasi nilai ekstrem yang saling bertentangan → submit | Sistem menolak lanjut, tampilkan pesan minta input ulang, tidak menyimpan hasil sebagai valid |
| BB-06 | M4 Validasi CR | Pengguna mengisi matriks konsisten (CR < 0,1) | Isi dengan nilai dari kasus uji PRD-03 §3.3 → submit | Sistem lanjut ke halaman hasil, bobot kriteria sesuai golden test case |
| BB-07 | M5 Visualisasi Peta | Tombol tampilkan peta hasil rekomendasi | Setelah CR valid → klik "Lihat Peta" | Peta Leaflet.js termuat dengan marker sejumlah alternatif lokasi, terurut sesuai skor |
| BB-08 | M5 Visualisasi Peta | Klik marker lokasi | Klik salah satu marker di peta | Popup muncul menampilkan nama lokasi, skor akhir, rincian per kriteria |
| BB-09 | M6 Buffer Zone | Aktifkan buffer zone di satu titik | Klik lokasi → set radius 500m → aktifkan buffer | Lingkaran radius tergambar di peta, jumlah kompetitor dalam radius tampil dan sesuai data aktual |
| BB-10 | M6 Buffer Zone | Ubah radius buffer secara dinamis | Geser slider radius dari 500m ke 1000m | Lingkaran & jumlah kompetitor diperbarui tanpa reload halaman |
| BB-11 | M7 Komparasi Lokasi | Pengguna memilih 4 lokasi pribadi untuk dibandingkan | Tandai 4 titik di peta / pilih dari daftar → submit komparasi | Tabel/kartu perbandingan skor pribadi vs rekomendasi #1 sistem tampil lengkap |
| BB-12 | M7 Komparasi Lokasi | Pengguna mencoba memilih lokasi ke-5 | Tandai lokasi ke-5 setelah 4 sudah dipilih | Sistem mencegah, tampilkan pesan batas maksimal 4 lokasi |

**Kriteria lolos:** seluruh baris di atas menunjukkan hasil aktual = hasil diharapkan, dicatat dengan tangkapan layar sebagai bukti untuk lampiran skripsi.

---

## 2. Validasi Matematis AHP (Cross-Check dengan Excel)

**Tujuan:** membuktikan algoritma PHP di Laravel tidak punya kesalahan logika kalkulasi (Bab 3.4.4b).

**Protokol:**

1. Siapkan lembar kerja Excel dengan rumus manual: total kolom, normalisasi, rata-rata baris (bobot), λmax, CI, CR — persis mengikuti rumus di `PRD-03-Data-Model-AHP-Spec.md` §3.2.
2. Masukkan **kasus uji Laundry** (matriks di PRD-03 §3.3) ke sistem web dan ke Excel secara paralel.
3. Bandingkan baris demi baris:

| Output | Excel (manual) | Sistem Web | Identik? |
|---|---|---|---|
| Bobot Harga Sewa | 0,1608 | *diisi hasil aktual* | |
| Bobot Kepadatan Penduduk | 0,4661 | | |
| Bobot Kompetitor | 0,0958 | | |
| Bobot Keamanan | 0,2773 | | |
| λmax | 4,0129 | | |
| CI | 0,0043 | | |
| CR | 0,0048 | | |
| Skor akhir tiap alternatif lokasi | *(hitung manual dari data lokasi uji)* | | |

4. Ulangi dengan **minimal 2 kasus uji tambahan**: satu untuk Kafe (bobot berbeda karena preferensi kriteria berbeda), dan satu kasus yang sengaja tidak konsisten (CR ≥ 0,1) untuk memverifikasi sistem benar-benar menolaknya.

**Kriteria lolos:** seluruh angka identik 100% (toleransi pembulatan maksimal 4 desimal, karena proposal mensyaratkan presisi identik — bukan mendekati).

---

## 3. User Acceptance Testing (UAT)

**Peserta:** pelaku usaha laundry & kafe di Kecamatan Sumbersari + masyarakat umum sekitar wilayah (disarankan minimal 15–20 responden untuk hasil kuesioner yang cukup representatif pada skala skripsi).

**Prosedur:**
1. Peserta mengoperasikan sistem langsung tanpa panduan detail (simulasikan penggunaan nyata).
2. Setelah selesai mencoba seluruh alur (pilih jenis usaha → isi AHP → lihat peta → coba buffer zone → coba komparasi), peserta mengisi kuesioner Likert 1–5 (1 = Sangat Tidak Setuju, 5 = Sangat Setuju).

### Draf Kuesioner Likert

| # | Indikator | Pernyataan |
|---|---|---|
| 1 | Kemudahan pemahaman peta | Saya dengan mudah memahami informasi yang ditampilkan pada peta interaktif |
| 2 | Kemudahan pemahaman peta | Perbedaan warna/urutan marker rekomendasi lokasi mudah saya pahami |
| 3 | Kecepatan proses AHP | Proses pengisian perbandingan kriteria AHP tidak membingungkan |
| 4 | Kecepatan proses AHP | Saya bisa menyelesaikan proses input preferensi dalam waktu yang wajar |
| 5 | Kegunaan praktis | Sistem ini membantu saya mempertimbangkan lokasi usaha secara lebih terukur dibanding cara manual |
| 6 | Kegunaan praktis | Fitur perbandingan lokasi pilihan saya dengan rekomendasi sistem berguna dalam pengambilan keputusan |
| 7 | Kegunaan praktis | Saya akan menggunakan sistem ini lagi jika mencari lokasi usaha di masa depan |

**Analisis:** hitung rata-rata skor per indikator dan interpretasikan dengan skala interval standar (mis. 1,00–1,79 Sangat Kurang, hingga 4,20–5,00 Sangat Baik) — sesuaikan dengan pedoman skala Likert yang dipakai jurusanmu untuk konsistensi dengan skripsi lain di POLIJE.

**Kriteria lolos:** rata-rata skor tiap indikator berada di kategori "Baik" atau lebih tinggi.
