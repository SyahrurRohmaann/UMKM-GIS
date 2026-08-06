@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="font-display text-2xl font-bold">Data Jenis Usaha</h2>
    <a href="{{ route('admin.businesses.create') }}" class="bg-cyan-cetak text-kertas px-4 py-2 font-mono text-sm uppercase tracking-wide hover:bg-cyan-cetak/90">
        + Tambah Usaha
    </a>
</div>

<div class="bg-white border border-tinta/20 rounded-sm overflow-hidden">
    <table class="w-full text-left font-body text-sm">
        <thead class="bg-kertas border-b border-tinta/20 font-mono text-xs uppercase text-tinta/70">
            <tr>
                <th class="p-4">Nama Usaha</th>
                <th class="p-4">Slug</th>
                <th class="p-4 text-center">Jml Kriteria</th>
                <th class="p-4 text-center">Jml Alternatif</th>
                <th class="p-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($businesses as $b)
            <tr class="border-b border-tinta/10 hover:bg-black/5">
                <td class="p-4 font-semibold">{{ $b->name }}</td>
                <td class="p-4 font-mono text-xs text-tinta/60">{{ $b->slug }}</td>
                <td class="p-4 text-center">{{ $b->criteria_count }}</td>
                <td class="p-4 text-center">{{ $b->alternatives_count }}</td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.businesses.show', $b) }}" class="text-cyan-cetak hover:underline">Kelola Data</a>
                    <a href="{{ route('admin.businesses.edit', $b) }}" class="text-tinta/60 hover:text-tinta hover:underline">Edit</a>
                    <form action="{{ route('admin.businesses.destroy', $b) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus beserta seluruh kriteria & alternatifnya?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-karat hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection