@extends('admin.layouts.app')

@section('title', 'Master Data Kelurahan')

@push('styles')
<style>
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
    <h5 style="margin: 0; font-weight: 500;">Daftar Kelurahan</h5>
    <a href="{{ route('admin.kelurahan.create') }}" class="btn-custom">Tambah Baru</a>
</div>

<div class="card-custom" style="padding: 0; overflow: hidden;">
    <table class="table-custom">
        <thead style="background: var(--hover-bg);">
            <tr>
                <th>No</th>
                <th>Nama Kelurahan</th>
                <th>Kepadatan Penduduk (jiwa/km²)</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kelurahan as $index => $kel)
            <tr>
                <td>{{ $kelurahan->firstItem() + $index }}</td>
                <td>
                    <div style="font-weight: 500;">{{ $kel->nama }}</div>
                </td>
                <td style="font-family: 'IBM Plex Mono', monospace;">{{ number_format($kel->kepadatan_penduduk, 0, ',', '.') }}</td>
                <td style="text-align: right;">
                    <a href="{{ route('admin.kelurahan.edit', $kel->id) }}" style="color: #3B82F6; text-decoration: none; margin-right: 12px;">Edit</a>
                    <form action="{{ route('admin.kelurahan.destroy', $kel->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Yakin hapus kelurahan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: var(--danger-color); cursor: pointer; font-size: 14px; padding: 0;">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 32px;">Tidak ada data kelurahan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($kelurahan->hasPages())
<div style="margin-top: 24px;">
    {{ $kelurahan->withQueryString()->links('pagination::bootstrap-4') }}
</div>
@endif
@endsection
