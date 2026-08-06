<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root[data-theme="dark"] {
            --bg-color: #030303;
            --surface-color: #2A2A2E;
            --text-primary: #FFFFFF;
            --text-secondary: #A1A1AA;
            --border-color: #2A2A2E;
            --primary-color: #F97316;
            --primary-hover: #ea580c;
            --hover-bg: rgba(255, 255, 255, 0.02);
            --danger-color: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.1);
        }
        :root[data-theme="light"] {
            --bg-color: #F3F4F6;
            --surface-color: #FFFFFF;
            --text-primary: #111827;
            --text-secondary: #6B7280;
            --border-color: #E5E7EB;
            --primary-color: #F97316;
            --primary-hover: #ea580c;
            --hover-bg: rgba(0, 0, 0, 0.02);
            --danger-color: #DC2626;
            --danger-bg: rgba(220, 38, 38, 0.1);
        }

        body { 
            background: var(--bg-color); 
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar-admin { 
            height: 100vh; 
            background: var(--bg-color); 
            border-right: 1px solid var(--border-color);
            padding-top: 24px; 
            position: fixed; 
            width: 250px; 
            transition: background-color 0.3s, border-color 0.3s;
        }
        .sidebar-admin h4 {
            color: var(--text-primary);
            font-weight: 500;
            padding: 0 24px;
            margin-bottom: 32px;
            font-size: 16px;
            letter-spacing: 0.05em;
        }
        .sidebar-admin a { 
            color: var(--text-secondary); 
            text-decoration: none; 
            display: block; 
            padding: 12px 24px; 
            transition: all 0.2s; 
            font-size: 14px;
        }
        .sidebar-admin a:hover, .sidebar-admin a.active { 
            color: var(--primary-color); 
            background: var(--surface-color); 
        }
        .main-content { 
            margin-left: 250px; 
            padding: 40px; 
            min-height: 100vh;
        }
        .page-title {
            font-family: 'Inter', sans-serif;
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 32px;
            color: var(--text-primary);
        }
        .card-custom {
            background: var(--surface-color);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 24px;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .table-custom {
            width: 100%;
            text-align: left;
            border-collapse: collapse;
        }
        .table-custom th {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
        }
        .table-custom td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-primary);
        }
        .table-custom tr:hover td {
            background: var(--hover-bg);
        }
        .btn-custom {
            background: var(--primary-color);
            color: #030303;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-custom:hover {
            background: var(--primary-hover);
        }
        .btn-outline {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .btn-outline:hover {
            background: var(--surface-color);
            color: var(--text-primary);
        }
        .badge-custom {
            padding: 4px 8px;
            border-radius: 9999px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-kandidat { background: rgba(59, 130, 246, 0.2); color: #3B82F6; }
        .badge-kompetitor { background: rgba(249, 115, 22, 0.2); color: #F97316; }
        .theme-toggle {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 14px;
            user-select: none;
        }
        .theme-toggle:hover {
            color: var(--text-primary);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar-admin">
        <h4>SPK ADMIN</h4>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.alternatif.index') }}" class="{{ request()->routeIs('admin.alternatif.*') ? 'active' : '' }}">Alternatif Lokasi</a>
        <a href="{{ route('admin.kriteria.index') }}" class="{{ request()->routeIs('admin.kriteria.*') ? 'active' : '' }}">Kriteria AHP</a>
        <div style="padding: 24px;">
            <hr style="border-color: var(--border-color); margin: 0; transition: border-color 0.3s;">
        </div>
        <a href="{{ route('map.index') }}">Lihat Peta (Publik)</a>
        
        <div style="margin-top: auto;">
            <div class="theme-toggle" onclick="toggleTheme()">
                <span id="theme-icon">🌙</span>
                <span id="theme-text">Mode Malam</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="padding: 0 24px 24px 24px;">
                @csrf
                <button type="submit" class="btn-custom btn-outline" style="width: 100%;">Logout</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <h3 class="page-title">@yield('title')</h3>
        
        @if(session('success'))
            <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid #3B82F6; color: #3B82F6; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            const isDark = theme === 'dark';
            document.getElementById('theme-icon').textContent = isDark ? '🌙' : '☀️';
            document.getElementById('theme-text').textContent = isDark ? 'Mode Malam' : 'Mode Siang';
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        }

        // Initialize theme from localStorage or default to dark
        const savedTheme = localStorage.getItem('theme') || 'dark';
        setTheme(savedTheme);
    </script>
    @stack('scripts')
</body>
</html>
