@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 24px;">
    <div class="card-custom">
        <h5 style="color: var(--text-secondary); font-size: 14px; font-weight: 400; margin: 0 0 8px 0;">Kandidat Lokasi</h5>
        <h2 style="font-size: 32px; margin: 0; font-weight: 500; color: var(--text-primary);">{{ $countLokasi }}</h2>
    </div>
    <div class="card-custom">
        <h5 style="color: var(--text-secondary); font-size: 14px; font-weight: 400; margin: 0 0 8px 0;">Titik Kompetitor</h5>
        <h2 style="font-size: 32px; margin: 0; font-weight: 500; color: var(--text-primary);">{{ $countKompetitor }}</h2>
    </div>
    <div class="card-custom">
        <h5 style="color: var(--text-secondary); font-size: 14px; font-weight: 400; margin: 0 0 8px 0;">Kelurahan</h5>
        <h2 style="font-size: 32px; margin: 0; font-weight: 500; color: var(--text-primary);">{{ $countKelurahan }}</h2>
    </div>
</div>
<div class="card-custom" style="background: rgba(59, 130, 246, 0.05); border-color: rgba(59, 130, 246, 0.2);">
    <p style="margin: 0; color: var(--text-secondary); line-height: 1.6;">
        Selamat datang di Panel Admin. Gunakan menu di samping untuk mengelola Master Data yang akan digunakan mesin AHP dan Peta Web-GIS.
    </p>
</div>
@endsection
