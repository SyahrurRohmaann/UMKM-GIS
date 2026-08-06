@extends('admin.layouts.app')

@section('title', 'Master Data Alternatif Lokasi')

@push('styles')
<style>
    .filter-container {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        background: var(--surface-color);
        padding: 16px;
        border-radius: 12px;
        align-items: flex-end;
        border: 1px solid var(--border-color);
        transition: background-color 0.3s, border-color 0.3s;
    }
    .form-group {
        flex: 1;
    }
    .form-group label {
        display: block;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        background: var(--bg-color);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        transition: background-color 0.3s, border-color 0.3s, color 0.3s;
    }
    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 24px 0 0 0;
        justify-content: center;
    }
    .page-item .page-link {
        padding: 8px 12px;
        background: var(--surface-color);
        color: var(--text-primary);
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        border: 1px solid var(--border-color);
    }
    .page-item.active .page-link {
        background: var(--primary-color);
        color: #030303;
        border-color: var(--primary-color);
    }
    .page-item.disabled .page-link {
        opacity: 0.5;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h5 style="margin: 0; font-weight: 500;">Daftar Titik Lokasi</h5>
    <a href="{{ route('admin.alternatif.create') }}" class="btn-custom">Tambah Baru</a>
</div>

<form method="GET" action="{{ route('admin.alternatif.index') }}" class="filter-container" id="filterForm">
    <div class="form-group">
        <label>Kelurahan</label>
        <select name="kelurahan" class="form-control" onchange="document.getElementById('filterForm').submit()">
            <option value="">Semua Kelurahan</option>
            @foreach($kelurahanList as $k)
                <option value="{{ $k->id }}" {{ request('kelurahan') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Jenis Usaha</label>
        <select name="jenis_usaha" class="form-control" onchange="document.getElementById('filterForm').submit()">
            <option value="">Semua Usaha</option>
            @foreach($jenisUsahaList as $j)
                <option value="{{ $j->id }}" {{ request('jenis_usaha') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Tipe</label>
        <select name="tipe" class="form-control" onchange="document.getElementById('filterForm').submit()">
            <option value="">Semua Tipe</option>
            <option value="kandidat" {{ request('tipe') == 'kandidat' ? 'selected' : '' }}>Kandidat</option>
            <option value="kompetitor" {{ request('tipe') == 'kompetitor' ? 'selected' : '' }}>Kompetitor</option>
        </select>
    </div>
    <div>
        <noscript>
            <button type="submit" class="btn-custom" style="height: 38px;">Filter</button>
        </noscript>
        @if(request()->anyFilled(['kelurahan', 'jenis_usaha', 'tipe']))
            <a href="{{ route('admin.alternatif.index') }}" class="btn-custom btn-outline" style="height: 38px; line-height: 20px; display: inline-block;">Reset</a>
        @endif
    </div>
</form>

<div class="card-custom" style="padding: 0; overflow: hidden;">
    <table class="table-custom">
        <thead style="background: var(--hover-bg);">
            <tr>
                <th>No</th>
                <th>Nama Lokasi</th>
                <th>Jenis Usaha</th>
                <th>Kelurahan</th>
                <th>Sewa (Rp)</th>
                <th>Keamanan</th>
                <th>Tipe</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lokasi as $index => $loc)
            <tr>
                <td>{{ $lokasi->firstItem() + $index }}</td>
                <td>
                    <div style="font-weight: 500;">{{ $loc->nama_lokasi }}</div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-top: 4px;">{{ $loc->latitude }}, {{ $loc->longitude }}</div>
                </td>
                <td>{{ $loc->jenis_usaha }}</td>
                <td>{{ $loc->kelurahan }}</td>
                <td style="font-family: 'IBM Plex Mono', monospace;">{{ number_format($loc->harga_sewa_per_tahun, 0, ',', '.') }}</td>
                <td>{{ $loc->skor_keamanan > 0 ? $loc->skor_keamanan : '-' }}</td>
                <td>
                    @if($loc->adalah_kompetitor)
                        <span class="badge-custom badge-kompetitor">Kompetitor</span>
                    @else
                        <span class="badge-custom badge-kandidat">Kandidat</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <a href="{{ route('admin.alternatif.edit', $loc->id) }}" style="color: #3B82F6; text-decoration: none; margin-right: 12px;">Edit</a>
                    <form action="{{ route('admin.alternatif.destroy', $loc->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Yakin hapus lokasi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: var(--danger-color); cursor: pointer; font-size: 14px; padding: 0;">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 32px;">Tidak ada data lokasi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($lokasi->hasPages())
<div style="margin-top: 24px;">
    {{ $lokasi->withQueryString()->links('pagination::bootstrap-4') }}
</div>
@endif
@endsection
