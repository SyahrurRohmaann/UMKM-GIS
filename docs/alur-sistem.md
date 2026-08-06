# Alur Sistem Pemilihan Lokasi Usaha

Berdasarkan `flowchart.png`, berikut adalah alur sistem (direpresentasikan dengan teks dan diagram Mermaid):

## Diagram Alur (Mermaid)

```mermaid
flowchart TD
    A([Mulai]) --> B[/User Memilih Jenis Usaha/]
    B --> C[Sistem Memuat Data Kriteria dan Bobot Nilai Sesuai Jenis Usaha]
    C --> D{Memilih Mode}

    D -- User Memilih Manual --> E[/User Menandai Lokasi di Peta Web-GIS/]
    D -- User Memilih Rekomendasi Sistem --> F[/Rekomendasi Sistem/]

    E --> G{Berapa Jumlah Lokasi yang Dipilih?}

    G -- Jika memilih 1 lokasi --> H[Ambil data minimal 3 lokasi terbaik yang tersimpan di basis data sistem.]
    F --> H

    G -- Jika memilih 2-4 lokasi --> I{Munculkan Opsi: Ikutsertakan Rekomendasi Sistem?}

    I -- Ya --> J[Bandingkan 4 lokasi pilihan user + data lokasi terbaik sistem.]
    I -- Tidak --> K[Bandingkan 4 lokasi pilihan user saja.]

    H --> L[Bandingkan kriteria 1 lokasi pilihan user dengan minimal 3 lokasi terbaik sistem menggunakan metode AHP.]

    J --> M[Hitung Perankingan Akhir AHP dan Proses Visualisasi Spasial<br/>Buffer Zone & Layer Peta Leaflet.js]
    K --> M
    L --> M

    M --> N[/Tampilkan Peta Interaktif, Marker Rekomendasi, dan Tabel Hasil Perbandingan Lokasi/]
    N --> O([Selesai])
```

## Penjelasan Teks Langkah Demi Langkah

1. **Mulai**
2. **Input User**: Pengguna memilih jenis usaha (Laundry / Kafe).
3. **Proses Sistem**: Sistem memuat data kriteria dan bobot nilai sesuai dengan jenis usaha yang dipilih.
4. **Keputusan Mode**: Pengguna memilih mode pencarian lokasi:
   - **Mode Manual**: Pengguna menandai lokasi langsung di peta Web-GIS.
   - **Mode Rekomendasi Sistem**: Sistem otomatis masuk ke skenario perbandingan data internal.
5. **Percabangan Mode Manual**: Berdasarkan jumlah lokasi yang ditandai:
   - **Jika 1 lokasi**: Sistem mengambil minimal 3 lokasi terbaik di database, lalu membandingkan 1 lokasi pengguna dengan 3 lokasi sistem.
   - **Jika 2-4 lokasi**: Sistem memunculkan opsi "Ikutsertakan Rekomendasi Sistem?".
     - **Ya**: Bandingkan lokasi pilihan user (max 4) ditambah data lokasi terbaik sistem.
     - **Tidak**: Bandingkan lokasi pilihan user saja.
6. **Percabangan Mode Rekomendasi**: Sistem mengambil minimal 3 lokasi terbaik di database dan langsung masuk ke proses perbandingan AHP.
7. **Proses Perhitungan Utama**: Sistem menghitung perankingan akhir menggunakan metode AHP dan melakukan proses visualisasi spasial (membuat Buffer Zone dan merender layer peta menggunakan Leaflet.js).
8. **Output**: Menampilkan peta interaktif, marker rekomendasi lokasi terbaik, dan tabel hasil perbandingan kriteria antar lokasi.
9. **Selesai**
