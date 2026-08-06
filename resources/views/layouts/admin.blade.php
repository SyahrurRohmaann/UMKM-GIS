<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - GIS AHP</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-kertas flex text-tinta font-body">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-tinta/20 flex flex-col">
        <div class="p-6 border-b border-tinta/20">
            <h1 class="font-display text-xl font-bold">Admin Panel</h1>
            <p class="font-mono text-xs text-tinta/60 mt-1">GIS AHP Sumbersari</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('admin.businesses.index') }}" class="block px-4 py-2 hover:bg-cyan-cetak/10 rounded font-semibold {{ request()->routeIs('admin.businesses.*') ? 'text-cyan-cetak' : '' }}">
                Jenis Usaha
            </a>
            <a href="{{ url('/') }}" class="block px-4 py-2 hover:bg-cyan-cetak/10 rounded font-semibold text-tinta/70 mt-8">
                &larr; Ke Peta Depan
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="p-8 max-w-5xl mx-auto">
            @if(session('success'))
                <div class="mb-4 p-4 bg-lumut/20 border border-lumut text-tinta rounded-sm font-mono text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>