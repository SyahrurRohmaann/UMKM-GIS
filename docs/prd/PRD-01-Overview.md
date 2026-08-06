# PRD — SPK Pemilihan Lokasi Usaha (AHP + Web-GIS)
## Dokumen 1/4: Overview Produk & Ruang Lingkup

**Sumber:** Proposal Skripsi *"Integrasi GIS Berbasis Web dan AHP untuk Pemilihan Lokasi Strategis Usaha Baru di Kecamatan Sumbersari Kota Jember"* — Mohammad Syahrur Rohman (E41231687), Teknik Informatika, POLIJE.
**Dokumen terkait:** PRD-02 (Functional Requirements), PRD-03 (Data Model & AHP Spec), PRD-04 (Non-Functional & Technical Spec).

---

## 1. Latar Belakang

Pemilihan lokasi usaha yang dilakukan secara manual rawan salah keputusan karena minim pertimbangan kondisi fisik lahan, lingkungan, dan kepadatan wilayah. Sistem ini dibangun untuk mengganti proses itu dengan Sistem Pendukung Keputusan (SPK) yang menggabungkan metode **AHP** (pembobotan kriteria terukur, teruji konsistensi) dengan **Web-GIS** (visualisasi spasial interaktif), sehingga calon pengusaha bisa melihat *dan* menghitung, bukan salah satu saja.

Fokus produk: dua jenis usaha (**Laundry** dan **Kafe**) di **Kecamatan Sumbersari, Kabupaten Jember** — wilayah yang dipilih karena jadi pusat pendidikan dan permukiman padat.

## 2. Rumusan Masalah → Pertanyaan Produk

| # | Rumusan Masalah (Sempro) | Diterjemahkan jadi kebutuhan produk |
|---|---|---|
| 1 | Bagaimana menentukan kriteria & bobot kepentingan lokasi secara objektif dan terukur? | Sistem harus punya form input perbandingan berpasangan AHP (skala Saaty 1–9) per jenis usaha, dengan uji konsistensi otomatis |
| 2 | Bagaimana merancang SPK yang mengintegrasikan AHP dengan Web-GIS untuk data spasial Jember? | Backend menghitung skor AHP → hasil dikirim ke peta interaktif (Leaflet.js) sebagai marker berperingkat |
| 3 | Bagaimana menyajikan komparasi otomatis antara lokasi pilihan pengguna vs rekomendasi terbaik sistem? | Fitur khusus: pengguna pilih 1–4 lokasi sendiri, sistem tampilkan perbandingan skor terhadap lokasi rekomendasi tersimpan |

## 3. Tujuan Produk

**Tujuan utama:** merancang dan membangun SPK yang mengintegrasikan algoritma AHP ke dalam arsitektur Web-GIS untuk pemilihan lokasi strategis usaha Laundry dan Kafe di Kecamatan Sumbersari.

Tujuan turunan:
- Menghasilkan bobot kriteria yang objektif dan teruji konsistensi (CR < 0,1) untuk tiap jenis usaha.
- Memvisualisasikan hasil perankingan lokasi secara spasial, bukan hanya tabel angka.
- Menyediakan analisis jangkauan kompetitor (*buffer zone*) secara real-time.
- Memungkinkan pengguna membandingkan opsi lokasi pribadinya terhadap rekomendasi sistem.

## 4. Ruang Lingkup (In Scope)

- Dua jenis usaha: **Laundry** dan **Kafe/F&B** — dipilih pengguna di awal alur sebagai *action trigger* yang menentukan set bobot kriteria yang dipakai.
- Wilayah: **Kecamatan Sumbersari, Kabupaten Jember** saja.
- Metode pembobotan & perankingan: **AHP** dengan syarat **Consistency Ratio (CR) < 0,1** — di atas itu, sistem menolak dan meminta input ulang.
- 4 kriteria umum (definisi lengkap di PRD-03): Harga Sewa Lahan/Bangunan, Kepadatan Penduduk, Kedekatan dengan Kompetitor Sejenis, Tingkat Keamanan Lingkungan. Bobot dihitung **terpisah** untuk Laundry vs Kafe.
- Pengguna dapat memilih **1 sampai 4 lokasi pribadi** untuk dibandingkan langsung dengan data lokasi terbaik yang tersimpan di sistem.
- Visualisasi peta interaktif berbasis Leaflet.js dengan overlay batas kelurahan (GeoJSON) Kecamatan Sumbersari.

## 5. Di Luar Ruang Lingkup (Out of Scope)

- Jenis usaha selain Laundry dan Kafe.
- Wilayah di luar Kecamatan Sumbersari.
- Metode pembobotan selain AHP (mis. TOPSIS, SAW) — tidak dikombinasikan pada versi ini.
- Transaksi bisnis, pembayaran, atau booking lokasi.
- Rekomendasi otomatis tanpa melalui input & validasi AHP pengguna (sistem tidak menebak preferensi pengguna).
- Update data kepadatan penduduk/kompetitor secara otomatis dari sumber eksternal (BPS) — pada versi ini data diinput manual oleh Admin.

## 6. Aktor / Persona

| Aktor | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Admin** | Pengelola data — kemungkinan besar peneliti/pemilik sistem | Kelola data spasial wilayah, titik koordinat alternatif lokasi, dan atribut kriteria (harga sewa, kompetitor, dst.) untuk kedua jenis usaha |
| **Pelaku Usaha / Calon Pengusaha (User)** | Calon pengusaha laundry atau kafe yang mencari lokasi strategis, warga umum Kecamatan Sumbersari | Input preferensi kriteria AHP, lihat peta rekomendasi, gunakan buffer zone, bandingkan lokasi pilihan sendiri |

*Asumsi:* Pelaku Usaha mengakses sistem tanpa perlu akun/login (akses publik) — hanya Admin yang memerlukan autentikasi karena mengelola data master. Ini asumsi kerja; validasikan ke pembimbing jika perlu login untuk User juga (misalnya untuk menyimpan riwayat komparasi).

## 7. Metrik Keberhasilan

| Metrik | Target | Sumber |
|---|---|---|
| Akurasi perhitungan AHP | 100% identik dengan perhitungan manual di Excel (cross-validation) | Bab 3.4.4 — Pengujian Validitas Metode |
| Konsistensi input pengguna | Sistem menolak submit jika CR ≥ 0,1 dan mengarahkan pengguna mengulang | Batasan Masalah poin c |
| Fungsionalitas fitur inti | Lolos Black Box Testing pada: manajemen data alternatif lokasi, form matriks AHP, tombol visualisasi peta, buffer zone + popup marker | Bab 3.4.4 |
| Penerimaan pengguna (UAT) | Skor Likert positif pada: kemudahan pemahaman informasi peta, kecepatan pemrosesan keputusan AHP, kegunaan praktis sistem | Bab 3.4.4 |

## 8. Asumsi & Ketergantungan

- Data kepadatan penduduk per kelurahan bersumber dari data sekunder BPS Kecamatan Sumbersari.
- Data tingkat keamanan lingkungan bersifat kualitatif — diperoleh dari observasi langsung + wawancara singkat warga, dikonversi ke skala 1–4 (lihat PRD-03).
- Data bobot kepentingan awal (jika diperlukan sebagai referensi/nilai default) diperoleh dari kuesioner/wawancara dengan pelaku usaha berpengalaman di Kecamatan Sumbersari.
- Batas wilayah kelurahan Kecamatan Sumbersari tersedia dalam format GeoJSON/Shapefile.
- Sinkron dengan Rencana Tata Ruang Wilayah (RTRW) Kabupaten Jember 2024–2044 sebagai rujukan kebijakan, bukan sumber data langsung sistem.
- Jangka waktu pengerjaan ±6 bulan mengikuti metode Waterfall (Requirement → Design → Implementation → Verification → Maintenance).

## 9. Catatan Konsistensi Judul

Judul sampul proposal ("...Kecamatan Sumbersari Kota Jember") dan judul di Bab 3.1 ("...Kota Jember Menggunakan Metode AHP Berbasis Web-GIS") sedikit berbeda susunan. PRD ini memakai wilayah **Kecamatan Sumbersari, Kabupaten Jember** secara konsisten sesuai Batasan Masalah — sebaiknya samakan juga penamaan di seluruh bab skripsi sebelum sidang.
