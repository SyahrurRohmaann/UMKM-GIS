@extends('layouts.app')

@section('content')
<div class="w-full h-full overflow-y-auto p-8 flex flex-col items-center">
    <div class="max-w-3xl w-full">
        <h1 class="font-display text-3xl font-bold mb-6">Pilih Jenis Usaha</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($businesses as $b)
                <a href="{{ url('/map/' . $b->slug) }}" class="p-6 bg-white border border-tinta/20 hover:border-cyan-cetak hover:shadow-sm transition-all group flex flex-col gap-4">
                    <h2 class="font-display text-2xl font-semibold text-tinta group-hover:text-cyan-cetak">{{ $b->name }}</h2>
                    <p class="font-mono text-sm text-tinta/60">Hitung rekomendasi lokasi untuk usaha {{ strtolower($b->name) }}</p>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection