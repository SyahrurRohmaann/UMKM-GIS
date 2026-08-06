@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-end">
    <div>
        <a href="{{ route('admin.businesses.index') }}" class="text-tinta/60 hover:text-cyan-cetak text-sm">&larr; Kembali ke daftar usaha</a>
        <h2 class="font-display text-3xl font-bold mt-2 text-tinta">Kelola Usaha: {{ $business->name }}</h2>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Panel Kriteria -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-display text-xl font-bold border-b-2 border-karat inline-block pb-1">Kriteria AHP</h3>
            <a href="{{ route('admin.businesses.criteria.create', $business) }}" class="text-cyan-cetak text-sm font-semibold hover:underline">+ Tambah Kriteria</a>
        </div>
        
        <div class="bg-white border border-tinta/20">
            <table class="w-full text-left text-sm">
                <thead class="bg-kertas font-mono text-xs text-tinta/70 border-b border-tinta/20">
                    <tr>
                        <th class="p-3">Kode</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Tipe</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($business->criteria as $c)
                    <tr class="border-b border-tinta/10">
                        <td class="p-3 font-mono text-xs">{{ $c->code }}</td>
                        <td class="p-3">{{ $c->name }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded-sm {{ $c->type == 'benefit' ? 'bg-lumut/20 text-lumut' : 'bg-karat/20 text-karat' }}">
                                {{ $c->type }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2 text-xs">
                            <a href="{{ route('admin.criteria.edit', $c) }}" class="text-cyan-cetak hover:underline">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-4 text-center text-tinta/50">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel Alternatif Lokasi -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-display text-xl font-bold border-b-2 border-karat inline-block pb-1">Alternatif Lokasi</h3>
            <a href="{{ route('admin.businesses.alternatives.create', $business) }}" class="text-cyan-cetak text-sm font-semibold hover:underline">+ Tambah Lokasi</a>
        </div>
        
        <div class="bg-white border border-tinta/20">
            <table class="w-full text-left text-sm">
                <thead class="bg-kertas font-mono text-xs text-tinta/70 border-b border-tinta/20">
                    <tr>
                        <th class="p-3">Nama Lokasi</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($business->alternatives as $a)
                    <tr class="border-b border-tinta/10">
                        <td class="p-3 font-semibold">{{ $a->name }}</td>
                        <td class="p-3 text-right space-x-2 text-xs">
                            <a href="{{ route('admin.alternatives.edit', $a) }}" class="text-cyan-cetak hover:underline">Edit & Skor</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="p-4 text-center text-tinta/50">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection