# SPK Pemilihan Lokasi Usaha — AHP + Web-GIS

Sistem Pendukung Keputusan berbasis Web-GIS yang mengintegrasikan metode *Analytic Hierarchy Process* (AHP) untuk membantu pemilihan lokasi strategis usaha **Laundry** dan **Kafe** di **Kecamatan Sumbersari, Kabupaten Jember**.

Skripsi — Mohammad Syahrur Rohman (E41231687), Teknik Informatika, Politeknik Negeri Jember.

## Peta Dokumen

Baca dengan urutan ini kalau kamu baru mulai:

| # | Dokumen | Isi | Untuk siapa |
|---|---|---|---|
| 1 | [`PRD-01-Overview.md`](./PRD-01-Overview.md) | Masalah, tujuan, ruang lingkup, persona, metrik sukses | Semua orang — baca ini duluan |
| 2 | [`PRD-02-Functional-Requirements.md`](./PRD-02-Functional-Requirements.md) | User story & acceptance criteria per modul (Admin & Pelaku Usaha) | Developer, pembimbing saat review Bab 3 |
| 3 | [`PRD-03-Data-Model-AHP-Spec.md`](./PRD-03-Data-Model-AHP-Spec.md) | ERD, definisi kriteria, rumus & contoh perhitungan AHP lengkap | Developer yang menulis mesin AHP |
| 4 | [`PRD-04-NonFunctional-Technical.md`](./PRD-04-NonFunctional-Technical.md) | Arsitektur, tech stack, kontrak API, kriteria pengujian | Developer, saat setup arsitektur |
| 5 | [`design.md`](./design.md) | Sistem desain visual — palet warna, tipografi, gaya peta, komponen UI | Developer frontend, siapa pun yang bikin mockup |
| 6 | [`TEST-PLAN.md`](./TEST-PLAN.md) | Skenario black box, protokol validasi Excel, kuesioner UAT | Untuk Bab 3.4.4 & Bab 4 skripsi |
| 7 | [`AGENTS.md`](./AGENTS.md) | Instruksi kerja untuk AI coding agent (struktur repo, aturan domain, konvensi kode) | Claude Code / AI agent lain yang bantu coding |

## Ringkasan Cepat

- **Aktor:** Admin (kelola data), Pelaku Usaha/Calon Pengusaha (pakai sistem, tanpa login).
- **Jenis usaha:** Laundry & Kafe — bobot kriteria dihitung terpisah untuk masing-masing.
- **4 kriteria tetap:** Harga Sewa Lahan/Bangunan, Kepadatan Penduduk, Kedekatan dengan Kompetitor Sejenis, Tingkat Keamanan Lingkungan.
- **Aturan keras:** Consistency Ratio (CR) harus < 0,1, atau sistem menolak dan minta input ulang.
- **Stack:** Laravel 12 (PHP) + MySQL di backend, Leaflet.js di peta.
- **Metode pengembangan:** Waterfall (Requirements → Design → Implementation → Verification → Maintenance), ±6 bulan.

## Status Proyek

Tahap: **dokumentasi perencanaan (PRD & desain) selesai, implementasi kode belum dimulai.** Struktur repo yang ditarget ada di `AGENTS.md`.

## Yang Masih Bisa Ditambahkan (opsional, buat kalau perlu)

Dokumen di atas sudah mencakup dari sisi produk, data, teknis, desain, dan pengujian. Yang sengaja belum dibuat karena butuh keputusan/konfirmasi kamu dulu:

- **Migrasi database Laravel aktual (.php)** — bisa saya buat begitu kamu konfirmasi struktur final tabel di PRD-03.
- **Mockup visual (wireframe/gambar)** — `design.md` sudah menjelaskan sistemnya secara tertulis; kalau butuh visual nyata (bukan cuma teks), saya bisa render mockup halaman peta + panel AHP.
- **Diagram ERD & Use Case bergambar** — proposal aslinya sudah punya versi gambar (Gambar 2.2, 3.2–3.5); saya bisa buat ulang versi digital yang konsisten dengan skema di PRD-03 kalau kamu mau ganti/lengkapi gambar yang ada.
- **Draf Bab 3 revisi** — menyelaraskan isi PRD-03/04 ini kembali ke format narasi skripsi (khususnya untuk mengisi bagian metodologi yang belum lengkap seperti rumus normalisasi alternatif di §3.4 PRD-03).

Bilang saja kalau salah satu dari ini mau dibuat sekarang.
