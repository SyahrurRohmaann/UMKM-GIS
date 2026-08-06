<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Lokasi - @yield('title')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { padding-top: 56px; overflow-x: hidden; }
        .wrapper { display: flex; width: 100%; height: calc(100vh - 56px); position: relative; }
        .sidebar {
            width: 350px;
            min-width: 350px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            padding: 20px;
            overflow-y: auto;
            transition: margin-left 0.3s ease-in-out;
            position: relative;
            z-index: 1000;
        }
        .sidebar.collapsed { margin-left: -350px; }
        
        .map-container { flex-grow: 1; position: relative; transition: all 0.3s; z-index: 1; }
        
        #sidebarToggle {
            position: absolute;
            top: 50%;
            left: 350px; /* Lebar sidebar */
            transform: translateY(-50%);
            z-index: 9999;
            background: white;
            border: 1px solid #ddd;
            border-left: none;
            border-radius: 0 8px 8px 0;
            padding: 15px 5px;
            cursor: pointer;
            box-shadow: 2px 0px 5px rgba(0,0,0,0.1);
            transition: left 0.3s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
        }
        #sidebarToggle:hover {
            background-color: #f4f4f4;
            color: #000;
        }
        
        /* Ketika sidebar disembunyikan, geser tombol ke kiri ujung */
        .sidebar.collapsed ~ #sidebarToggle {
            left: 0;
        }
    </style>
    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">SPK Lokasi AHP-GIS</a>
        </div>
    </nav>

    <div class="wrapper"><!-- Sidebar --><div class="sidebar" id="sidebar">@yield('sidebar')</div><button id="sidebarToggle" title="Toggle Sidebar"><i class="bi bi-chevron-left" id="toggleIcon"></i></button><!-- Map View --><div class="map-container" id="mapContainer">@yield('content')</div></div>

    <div class="modal fade" id="customLocModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Lokasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Sistem akan otomatis menghitung jumlah kompetitor (500m) dan kepadatan penduduk dari titik ini.</p>
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
                    <button type="button" class="btn btn-primary" id="btnSaveCustom">Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#sidebarToggle').click(function(e) {
                e.preventDefault();
                $('#sidebar').toggleClass('collapsed');
                
                // Ganti Icon
                if ($('#sidebar').hasClass('collapsed')) {
                    $('#toggleIcon').removeClass('bi-chevron-left').addClass('bi-chevron-right');
                } else {
                    $('#toggleIcon').removeClass('bi-chevron-right').addClass('bi-chevron-left');
                }
                
                // Beri waktu animasi CSS selesai, lalu paksa Leaflet merender ulang petanya
                setTimeout(() => {
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new Event('resize'));
                    }
                }, 350); 
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

