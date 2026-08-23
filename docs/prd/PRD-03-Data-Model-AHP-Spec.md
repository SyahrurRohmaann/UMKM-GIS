# PRD — SPK Pemilihan Lokasi Usaha (AHP + Web-GIS)
## Dokumen 3/4: Data Model & Spesifikasi Mesin AHP

Dokumen ini untuk developer yang mengimplementasikan skema database dan algoritma AHP di Laravel/PHP. Angka contoh diambil langsung dari proposal (Bab 2.2.2, Tabel 2.3–2.9) sehingga bisa dipakai sebagai *test case* validasi.

---

## 1. Struktur Data (ERD Ringkas)

```
Kriteria (1) ───< MatriksPerbandingan >─── (1) Kriteria
     │                                              (self-relation: kriteria_a_id, kriteria_b_id)
     │
     ├──< BobotKriteria >── JenisUsaha (Laundry / Kafe)
     │
JenisUsaha (1) ───< AlternatifLokasi >─── (1) Kelurahan
                          │
                          ├──< AtributLokasi (harga_sewa, skor_keamanan, jumlah_kompetitor)
                          │
                          └──< HasilPerankingan >── SesiPerhitungan

SesiPerhitungan (1) ───< LokasiPilihanUser (komparasi, max 4 baris per sesi)
```

### 1.1 Tabel `jenis_usaha`
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| nama | VARCHAR | "Laundry" / "Kafe" |

### 1.2 Tabel `kriteria`
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| nama | VARCHAR | Harga Sewa Lahan/Bangunan, Kepadatan Penduduk, Kedekatan dengan Kompetitor Sejenis, Tingkat Keamanan Lingkungan |
| deskripsi | TEXT | Definisi operasional (lihat Bagian 3) |

### 1.3 Tabel `kelurahan`
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| nama | VARCHAR | Nama kelurahan di Kec. Sumbersari |
| geojson_boundary | JSON/TEXT | Polygon batas wilayah |
| kepadatan_penduduk | FLOAT | jiwa/km², sumber data sekunder BPS |

### 1.4 Tabel `alternatif_lokasi`
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| jenis_usaha_id | INT (FK) | Laundry / Kafe / bisa dua-duanya via tabel pivot jika lokasi relevan untuk keduanya |
| kelurahan_id | INT (FK) | |
| nama_lokasi | VARCHAR | Label untuk ditampilkan di popup |
| latitude | DOUBLE | Presisi tinggi wajib untuk akurasi peta |
| longitude | DOUBLE | |
| harga_sewa_per_tahun | DECIMAL | Rupiah |
| skor_keamanan | TINYINT | 1–4, lihat Tabel Skala Keamanan (Bagian 3) |
| adalah_kompetitor | BOOLEAN | true jika baris ini merepresentasikan kompetitor eksisting, bukan kandidat lokasi baru |

### 1.5 Tabel `matriks_perbandingan`
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| sesi_id | INT (FK) | Tertaut ke sesi input pengguna |
| jenis_usaha_id | INT (FK) | |
| kriteria_a_id | INT (FK) | |
| kriteria_b_id | INT (FK) | |
| nilai_saaty | DECIMAL | 1/9 s.d. 9, sesuai skala Saaty |

### 1.6 Tabel `bobot_kriteria`
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| sesi_id | INT (FK) | |
| kriteria_id | INT (FK) | |
| bobot | DECIMAL(6,4) | Hasil eigenvector (0–1) |
| consistency_ratio | DECIMAL(6,4) | Disimpan agar bisa diaudit ulang |

### 1.7 Tabel `hasil_perankingan`
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| sesi_id | INT (FK) | |
| alternatif_lokasi_id | INT (FK) | |
| skor_akhir | DECIMAL(6,4) | Penjumlahan bobot × nilai kriteria ternormalisasi |
| ranking | INT | Urutan hasil, 1 = terbaik |

### 1.8 Tabel `lokasi_pilihan_user` (untuk fitur M7 — komparasi)
| Field | Tipe | Keterangan |
|---|---|---|
| id | INT (PK) | |
| sesi_id | INT (FK) | |
| latitude / longitude | DOUBLE | Titik yang ditandai pengguna sendiri, atau FK ke `alternatif_lokasi` bila memilih dari daftar |
| skor_dihitung | DECIMAL(6,4) | Dihitung ulang dengan bobot sesi yang sama |

---

## 2. Definisi Operasional Kriteria

4 kriteria berlaku untuk kedua jenis usaha, tapi **bobotnya dihitung terpisah**:

| Kriteria | Definisi Operasional | Sumber Data |
|---|---|---|
| **Harga Sewa Lahan/Bangunan** | Biaya sewa yang berdampak langsung pada modal awal & operasional jangka panjang | Input Admin, Rp/tahun |
| **Kepadatan Penduduk** | Indikator ukuran pasar & potensi permintaan; wilayah padat = eksposur pelanggan lebih besar | Data sekunder BPS per kelurahan, jiwa/km² |
| **Kedekatan dengan Kompetitor Sejenis** | Mengukur tingkat kejenuhan pasar dalam radius tertentu, dihitung terpisah sesuai jenis usaha yang dipilih pengguna | Dihitung sistem dari data `alternatif_lokasi` bertanda `adalah_kompetitor` dalam radius buffer |
| **Tingkat Keamanan Lingkungan** | Kriteria kualitatif tidak terstruktur — memengaruhi risiko kehilangan aset & kenyamanan konsumen | Observasi langsung + wawancara singkat warga, dikonversi ke skala 1–4 |

### Skala Skor Tingkat Keamanan Lingkungan

| Skor | Keterangan |
|---|---|
| 1 (Rawan) | Lingkungan gelap malam hari, sepi, jauh dari permukiman aktif, atau pernah ada kriminalitas |
| 2 (Cukup Aman) | Area ramai, tapi penerangan jalan minim malam hari |
| 3 (Aman) | Penerangan jalan baik, berada di area permukiman aktif/ramai |
| 4 (Sangat Aman) | Penerangan sangat baik, terpantau CCTV lingkungan, atau dekat pos keamanan/ronda aktif |

---

## 3. Spesifikasi Mesin AHP

### 3.1 Skala Perbandingan Saaty

| Nilai | Arti |
|---|---|
| 1 | Sama penting |
| 3 | Sedikit lebih penting |
| 5 | Lebih penting |
| 7 | Jelas lebih penting |
| 9 | Mutlak sangat penting |
| 2,4,6,8 | Nilai kompromi antar dua tingkat di atas |
| Kebalikan (1/3, 1/5, dst.) | Jika kriteria i dibanding j = a, maka j dibanding i = 1/a |

### 3.2 Alur Perhitungan (untuk diimplementasikan di PHP/Laravel)

**Langkah 1 — Susun matriks berpasangan** (array 2D n×n, n = jumlah kriteria = 4 pada sistem ini)

**Langkah 2 — Normalisasi matriks**: tiap sel dibagi dengan total kolomnya.
```
Xij = Cij / Σ(kolom j)
```

**Langkah 3 — Hitung bobot prioritas (eigenvector)**: rata-rata tiap baris matriks ternormalisasi.
```
Wi = Σ(baris i ternormalisasi) / n
```

**Langkah 4 — Hitung λmax**: kalikan tiap kolom matriks asli dengan bobot kriteria terkait, jumlahkan per baris, lalu bagi jumlah itu dengan bobot kriteria bersangkutan — λmax adalah rata-rata dari seluruh nilai konsistensi tersebut.

**Langkah 5 — Consistency Index (CI)**
```
CI = (λmax − n) / (n − 1)
```

**Langkah 6 — Consistency Ratio (CR)**
```
CR = CI / RI
```
RI diambil dari tabel Random Index berdasarkan n:

| n | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 |
|---|---|---|---|---|---|---|---|---|---|---|
| RI | 0,00 | 0,00 | 0,58 | **0,90** | 1,12 | 1,24 | 1,32 | 1,41 | 1,45 | 1,49 |

Untuk sistem ini **n = 4** → **RI = 0,90** tetap.

**Aturan bisnis:** jika `CR ≥ 0,1` → sistem melempar error dan meminta pengguna mengulang pengisian (lihat M4 di PRD-02). Jika `CR < 0,1` → lanjut ke perhitungan skor akhir alternatif lokasi.

### 3.3 Contoh Kasus Uji (dari proposal — pakai untuk unit test)

Studi kasus **Laundry**, 4 kriteria (Harga Sewa, Kepadatan Penduduk, Kompetitor, Keamanan):

**Matriks input:**

| | Biaya Sewa | Kepadatan Penduduk | Kompetitor | Keamanan |
|---|---|---|---|---|
| Biaya Sewa | 1 | 1/3 | 2 | 1/2 |
| Kepadatan Penduduk | 3 | 1 | 4 | 2 |
| Kompetitor | 1/2 | 1/4 | 1 | 1/3 |
| Keamanan | 2 | 1/2 | 3 | 1 |
| **Total kolom** | 6,5 | 2,08 | 10 | 3,83 |

**Bobot kriteria hasil (eigenvector):**
- Harga Sewa: **0,1608**
- Kepadatan Penduduk: **0,4661**
- Kompetitor: **0,0958**
- Keamanan: **0,2773**

**Uji konsistensi:**
- λmax = 4,0129
- CI = (4,0129 − 4) / (4 − 1) = **0,0043**
- RI (n=4) = 0,90
- CR = 0,0043 / 0,90 = **0,0048** → jauh di bawah 0,1, dinyatakan **konsisten**

Gunakan angka-angka ini sebagai *golden test case*. **Catatan verifikasi:** angka λmax/CI/CR pada proposal (λmax = 4,0129; CI = 0,0043; CR = 0,0048) berasal dari perhitungan manual di Excel yang membulatkan bobot lebih awal. Implementasi PHP (`AhpService`) menghitung eigenvector dengan metode rata-rata kolom-ternormalisasi tanpa pembulatan antara, sehingga menghasilkan λmax ≈ 4,031 dan CR ≈ 0,0115. **Kedua-duanya konsisten** (CR ≪ 0,1) dan bobotnya identik hingga 3 desimal (selisih < 0,001), jadi kesimpulan penelitian tidak berubah. Unit test (`tests/Unit/AhpServiceTest.php`) memakai delta 0,001 untuk bobot dan delta lebih longgar untuk CR/λmax guna mengakomodasi perbedaan pembulatan manual vs mesin ini. Bila skripsi menuntut kecocokan CR yang lebih ketat, sajikan perhitungan Excel dengan presisi penuh (tanpa pembulatan antara) di lampiran agar sama persis dengan output mesin.

### 3.4 Perhitungan Skor Akhir Alternatif Lokasi

Setelah bobot kriteria (`Wi`) didapat dan lolos uji CR, skor akhir tiap alternatif lokasi dihitung sebagai penjumlahan berbobot dari nilai ternormalisasi tiap kriteria pada lokasi tersebut:

```
Skor(lokasi) = Σ [ Wi × nilai_ternormalisasi(kriteria_i, lokasi) ]
```

Catatan implementasi (KEPUTUSAN FINAL): kriteria dengan arah "semakin tinggi semakin baik" (kepadatan penduduk, skor keamanan) dan "semakin rendah semakin baik" (harga sewa, jumlah kompetitor) dinormalisasi dengan **normalisasi min-max per kriteria**, arah dibalik untuk kriteria "semakin rendah semakin baik". Metode ini dipakai **secara seragam** di kedua jalur perhitungan sistem — `AhpService::hitungSkorAkhir` (endpoint `/api/ahp/calculate`) dan `ScoringService::calculateFinalScores` (endpoint `/api/recommendations/generate` yang dipakai peta) — agar hasil skor & ranking konsisten apa pun jalurnya. Rumus per kriteria:
- Benefit: `norm = (nilai − min) / (max − min)`
- Cost:    `norm = (max − nilai) / (max − min)`
- Bila `max = min` (semua alternatif bernilai sama pada kriteria itu), kriteria dianggap netral (`norm = 1` untuk semua) sehingga tidak membias ranking.
