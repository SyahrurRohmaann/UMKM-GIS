<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Lokasi - @yield('title')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Fira Sans (Body) & Fira Code (Numbers/Data) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: #059669;
            --color-secondary: #10B981;
            --color-accent: #EA580C;
            --color-background: #ECFDF5;
            --color-foreground: #064E3B;
            --color-muted: #E8F1F3;
            --color-border: #A7F3D0;
        }

        body { 
            font-family: 'Fira Sans', sans-serif;
            padding-top: 56px; 
            overflow-x: hidden; 
            background-color: #f8f9fa;
        }
        
        /* Font khusus angka/skor */
        .fira-code { font-family: 'Fira Code', monospace; }

        /* Bootstrap Overrides */
        .btn-primary, .btn-primary:active, .btn-primary:focus { background-color: var(--color-primary) !important; border-color: var(--color-primary) !important; color: #fff !important; }
        .btn-primary:hover { background-color: var(--color-foreground) !important; border-color: var(--color-foreground) !important; color: #fff !important; }
        .btn-success { background-color: var(--color-secondary); border-color: var(--color-secondary); }
        .btn-success:hover { background-color: var(--color-primary); border-color: var(--color-primary); }
        .bg-primary { background-color: var(--color-primary) !important; }
        .bg-success { background-color: var(--color-secondary) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .text-success { color: var(--color-secondary) !important; }
        
        .navbar-custom { background-color: var(--color-foreground) !important; }

        .wrapper { display: flex; width: 100%; height: calc(100vh - 56px); position: relative; }
        
        /* Sidebar layout map desktop */
        .sidebar-desktop {
            width: 350px;
            min-width: 350px;
            background: white;
            border-right: 1px solid var(--color-border);
            padding: 20px;
            overflow-y: auto;
            display: none; /* hidden on mobile, block on lg */
            z-index: 1000;
        }
        
        .map-container { flex-grow: 1; position: relative; z-index: 1; height: 100%; }

        @media (min-width: 992px) {
            .sidebar-desktop { display: block; }
            .mobile-toggle { display: none; }
        }
        
        @media (max-width: 991.98px) {
            .map-container { height: 100%; width: 100%; }
        }

        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.8);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
    </style>
    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container-fluid">
            <button class="btn btn-outline-light d-lg-none me-2 mobile-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="#">SPK Lokasi AHP-GIS</a>
            @auth
            <div class="d-flex">
                 <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-speedometer2"></i> Admin</a>
            </div>
            @endauth
        </div>
    </nav>

    <div class="wrapper">
        <!-- Sidebar Desktop -->
        <div class="sidebar-desktop shadow-sm" id="sidebarDesktop">
            @yield('sidebar')
        </div>

        <!-- Sidebar Mobile (Offcanvas) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title">Menu Panel</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                @yield('sidebar')
            </div>
        </div>

        <!-- Map View -->
        <div class="map-container" id="mapContainer">
            <div class="loading-overlay" id="mapLoading">
                <div class="spinner-border text-primary" role="status"></div>
                <span class="mt-2 fw-bold text-primary">Memproses Data...</span>
            </div>
            @yield('content')
        </div>
    </div>

    <!-- Modal Kustom Lokasi -->
    <div class="modal fade" id="customLocModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Lokasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Sistem otomatis hitung kompetitor (500m) & penduduk.</p>
                    <input type="hidden" id="c_lat">
                    <input type="hidden" id="c_lng">
                    <div class="mb-2">
                        <label>Nama Lokasi</label>
                        <input type="text" class="form-control" id="c_nama" value="Lokasi Kustom">
                    </div>
                    <div class="mb-2">
                        <label>Harga Sewa (Rp/Tahun)</label>
                        <input type="number" class="form-control" id="c_sewa" value="20000000">
                    </div>
                    <div class="mb-2">
                        <label>Skor Keamanan (1-4)</label>
                        <select class="form-select" id="c_aman">
                            <option value="1">1 - Rawan</option>
                            <option value="2">2 - Cukup Aman</option>
                            <option value="3" selected>3 - Aman</option>
                            <option value="4">4 - Sangat Aman</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveCustom">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="spinSaveCustom" role="status"></span>
                        Tambahkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    @stack('scripts')
</body>
</html>