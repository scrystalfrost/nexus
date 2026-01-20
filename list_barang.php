<?php
include 'db_connect.php';

$stmt = $pdo->query("SELECT * FROM Tb_Barang ORDER BY Id_barang DESC");
$barangList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data Analytics
$labels = []; $stokData = []; $totalAset = 0; $critical = 0;
foreach ($barangList as $b) {
    $labels[] = $b['Nama_barang'];
    $stokData[] = $b['Stok_barang'];
    $totalAset += ($b['Harga_barang'] * $b['Stok_barang']);
    if($b['Stok_barang'] < 5) $critical++;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Nexus Command Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #020617; 
            --card-dark: #0f172a; 
            --text-light: #f1f5f9; --text-gray: #94a3b8;
            --primary: #6366f1; --accent: #8b5cf6; --danger: #f43f5e; --success: #10b981;
            --glow: 0 0 25px rgba(99, 102, 241, 0.25);
            --sidebar-width: 80px;
            --sidebar-width-hover: 260px;
        }
        
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-dark); color: var(--text-light); overflow-x: hidden; transition: 0.3s; }
        
        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width); height: 100vh; position: fixed;
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255,255,255,0.05); padding: 20px 15px; z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; white-space: nowrap;
        }
        .sidebar:hover { width: var(--sidebar-width-hover); padding: 20px 25px; background: #0f172a; }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; transition: all 0.4s; min-height: 100vh; }
        
        /* Logo Styles */
        .brand-wrapper { display: flex; align-items: center; gap: 15px; margin-bottom: 40px; height: 50px; }
        .logo-box {
            width: 45px; height: 45px; background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: white; box-shadow: var(--glow); flex-shrink: 0;
            transition: 0.3s;
        }
        .sidebar:hover .logo-box { transform: rotate(10deg) scale(1.1); }
        .brand-text { 
            opacity: 0; transition: 0.3s; transform: translateX(-10px); 
            font-weight: 700; font-size: 1.4rem; letter-spacing: 1px;
            background: linear-gradient(90deg, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .sidebar:hover .brand-text { opacity: 1; transform: translateX(0); }
        
        /* Nav Links */
        .nav-link {
            color: var(--text-gray); padding: 12px; border-radius: 14px; margin-bottom: 8px;
            transition: 0.3s; display: flex; align-items: center; font-weight: 500; height: 50px;
            border: 1px solid transparent; cursor: pointer;
        }
        .nav-link i { min-width: 24px; font-size: 1.2rem; text-align: center; margin-right: 15px; display: flex; justify-content: center; align-items: center; transition: 0.3s; }
        .nav-text { opacity: 0; transition: 0.2s; }
        .sidebar:hover .nav-text { opacity: 1; }
        .nav-link:hover, .nav-link.active { 
            background: rgba(99, 102, 241, 0.1); color: white; 
            border-color: rgba(99, 102, 241, 0.2); box-shadow: 0 0 15px rgba(99, 102, 241, 0.1);
        }
        .nav-link.active i { color: var(--accent); transform: scale(1.1); text-shadow: 0 0 10px var(--accent); }

        /* Neon Cards */
        .neon-card {
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.03); border-radius: 24px;
            padding: 30px; transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            position: relative; overflow: hidden; height: 100%; display: flex; flex-direction: column;
        }
        .neon-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent 40%);
            pointer-events: none;
        }
        .neon-card:hover { transform: translateY(-5px); border-color: rgba(99, 102, 241, 0.2); box-shadow: 0 20px 50px rgba(0,0,0,0.3); }

        /* Table */
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-color: var(--text-gray); }
        .table-dark-custom th { border-bottom: 1px solid rgba(255,255,255,0.08); color: var(--text-light); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1.5px; padding-bottom: 20px; white-space: nowrap; }
        .table-dark-custom td { padding: 18px 10px; border-bottom: 1px solid rgba(255,255,255,0.02); vertical-align: middle; transition: 0.2s; }
        .table-dark-custom tr:hover td { color: white; background: rgba(255,255,255,0.02); }

        /* Buttons & Pills */
        .btn-neon {
            background: linear-gradient(135deg, var(--primary), var(--accent)); color: white; border: none;
            padding: 12px 28px; border-radius: 50px; font-weight: 600; letter-spacing: 0.5px;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3); transition: 0.3s; white-space: nowrap; text-decoration: none;
        }
        .btn-neon:hover { box-shadow: 0 6px 30px rgba(99, 102, 241, 0.5); color: white; transform: scale(1.05); }
        
        .btn-mode {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-gray);
            padding: 8px 16px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; transition: 0.3s; cursor: pointer;
        }
        .btn-mode:hover, .btn-mode.active { background: rgba(99, 102, 241, 0.2); color: white; border-color: var(--primary); }

        .status-pill { 
            padding: 6px 14px; border-radius: 30px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            display: inline-block; white-space: nowrap; min-width: 90px; text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-ok { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-bad { background: rgba(244, 63, 94, 0.1); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.2); box-shadow: 0 0 10px rgba(244, 63, 94, 0.1); }

        /* Datatable Styling */
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter { color: var(--text-gray) !important; margin-bottom: 20px; }
        .dataTables_wrapper .dataTables_info { color: var(--text-gray) !important; margin-top: 20px; }
        .dataTables_wrapper .dataTables_paginate { margin-top: 20px; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: white !important; background: transparent !important; border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 50% !important; width: 35px; height: 35px; display: inline-flex !important; align-items: center; justify-content: center; margin: 0 3px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary) !important; border-color: var(--primary) !important; box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }
        
        .form-control, .form-select { background: #0f172a; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 12px; padding: 10px 15px; }
        .form-control:focus { background: #0f172a; color: white; border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(99,102,241,0.25); }

        /* ANIMATION FOR VIEW SWITCH */
        .hidden-section { display: none !important; opacity: 0; }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        @media (max-width: 992px) { .sidebar { width: 0; padding: 0; } .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

    <nav class="sidebar d-none d-lg-block">
        <div class="brand-wrapper">
            <div class="logo-box">
                <i class="fas fa-network-wired"></i>
            </div>
            <div class="brand-text">NEXUS</div>
        </div>

        <div class="nav flex-column gap-2">
            <a onclick="switchView('dashboard')" class="nav-link active" id="nav-dash">
                <i class="fas fa-grid-2"></i> <span class="nav-text">Dashboard</span>
            </a>
            <a onclick="switchView('live')" class="nav-link" id="nav-inv">
                <i class="fas fa-cube"></i> <span class="nav-text">Live Data</span>
            </a>
            <a onclick="switchView('report')" class="nav-link" id="nav-rep">
                <i class="fas fa-chart-pie"></i> <span class="nav-text">Report</span>
            </a>
            <div class="mt-4 pt-3 border-top border-secondary border-opacity-10"></div>
            <a href="#" class="nav-link"><i class="fas fa-sliders-h"></i> <span class="nav-text">Settings</span></a>
        </div>
    </nav>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1 text-white" id="pageTitle" style="letter-spacing: -0.5px;">Dashboard</h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-3">System Online</span>
                </div>
            </div>
            
            <div class="d-flex gap-2 bg-dark p-1 rounded-4 border border-white border-opacity-10">
                <button onclick="switchView('dashboard')" class="btn-mode active" id="btn-dash"><i class="fas fa-columns me-2"></i>Split</button>
                <button onclick="switchView('live')" class="btn-mode" id="btn-inv"><i class="fas fa-table me-2"></i>Live Data</button>
                <button onclick="switchView('report')" class="btn-mode" id="btn-rep"><i class="fas fa-chart-bar me-2"></i>Report</button>
            </div>

            <a href="form_edit.php" class="btn-neon"><i class="fas fa-plus me-2"></i> ADD ITEM</a>
        </div>

        <div id="statsSection" class="row g-4 mb-5 fade-in">
            <div class="col-md-4">
                <div class="neon-card d-flex align-items-center justify-content-between" style="min-height: 120px;">
                    <div>
                        <p class="text-gray small fw-bold mb-1" style="letter-spacing:1px;">TOTAL ASSETS</p>
                        <h3 class="fw-bold text-white mb-0">Rp <?= number_format($totalAset/1000000, 1) ?> M</h3>
                    </div>
                    <div class="text-primary fs-1 opacity-25"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="neon-card d-flex align-items-center justify-content-between" style="min-height: 120px;">
                    <div>
                        <p class="text-gray small fw-bold mb-1" style="letter-spacing:1px;">ACTIVE SKUs</p>
                        <h3 class="fw-bold text-white mb-0"><?= count($barangList) ?> Items</h3>
                    </div>
                    <div class="text-success fs-1 opacity-25"><i class="fas fa-cubes"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="neon-card d-flex align-items-center justify-content-between" style="min-height: 120px; border-color: rgba(244, 63, 94, 0.3);">
                    <div>
                        <p class="text-danger small fw-bold mb-1" style="letter-spacing:1px;">ALERTS</p>
                        <h3 class="fw-bold text-danger mb-0"><?= $critical ?> Critical</h3>
                    </div>
                    <div class="text-danger fs-1 opacity-25"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-4" id="mainRow">
            <div class="col-xl-8 fade-in" id="tableCol">
                <div class="neon-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0 text-white"><i class="fas fa-database me-2 text-primary"></i> Live Data</h5>
                        <button class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="location.reload()"><i class="fas fa-sync-alt me-2"></i>Refresh</button>
                    </div>
                    <div class="table-responsive h-100">
                        <table id="myTable" class="table table-dark-custom align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Stock Level</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barangList as $b): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-3 bg-dark border border-secondary d-flex align-items-center justify-content-center text-secondary fw-bold flex-shrink-0" style="width:40px;height:40px;">
                                                <?= strtoupper(substr($b['Nama_barang'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-white text-truncate" style="max-width: 180px;"><?= $b['Nama_barang'] ?></div>
                                                <div class="small text-gray" style="font-size: 0.75rem;">ID: #<?= str_pad($b['Id_barang'], 4, '0', STR_PAD_LEFT) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-light fw-medium">Rp <?= number_format($b['Harga_barang'], 0, ',', '.') ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="fw-bold text-end text-white" style="width: 30px;"><?= $b['Stok_barang'] ?></span>
                                            <div class="progress bg-dark border border-secondary flex-grow-1" style="height: 6px; min-width: 80px; border-radius: 10px;">
                                                <div class="progress-bar <?= $b['Stok_barang']<5 ? 'bg-danger':'bg-info' ?>" 
                                                     role="progressbar" 
                                                     style="width: <?= min(100, $b['Stok_barang']*5) ?>%; box-shadow: 0 0 10px <?= $b['Stok_barang']<5 ? 'rgba(244,63,94,0.5)':'rgba(99,102,241,0.5)' ?>;">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= $b['Stok_barang'] < 5 ? '<span class="status-pill status-bad"><i class="fas fa-bolt me-1"></i>Low</span>' : '<span class="status-pill status-ok">Good</span>' ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="form_edit.php?id=<?= $b['Id_barang'] ?>" class="btn btn-sm btn-outline-light rounded-circle border-0 bg-white bg-opacity-10" style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center; transition:0.2s;"><i class="fas fa-pencil-alt fa-xs"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 fade-in" id="chartCol">
                <div class="neon-card">
                    <h5 class="fw-bold mb-4 text-white"><i class="fas fa-chart-area me-2 text-accent"></i> Stock Hologram</h5>
                    <div style="flex-grow: 1; min-height: 350px; position: relative;">
                        <canvas id="smartChart"></canvas>
                    </div>
                    <div class="mt-4 p-3 rounded-3 bg-dark border border-secondary border-opacity-50">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fw-bold text-white small">Insight</span>
                        </div>
                        <p class="mb-0 text-gray small lh-sm">
                            Visualizing <strong class="text-white"><?= count($barangList) ?> items</strong>. 
                            <span class="text-danger"><?= $critical ?> items</span> require immediate attention.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Init Tools
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            background: '#1e293b', color: '#fff', iconColor: '#6366f1'
        });

        $(document).ready(function() {
            $('#myTable').DataTable({ 
                dom: 'rtip', pageLength: 8, 
                language: { paginate: { next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' } }
            });
            
            // Notification Logic
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('status') === 'added') Toast.fire({ icon: 'success', title: 'New Item Added' });
            if(urlParams.get('status') === 'updated') Toast.fire({ icon: 'success', title: 'Item Updated' });
            if(urlParams.get('status')) window.history.replaceState(null, null, window.location.pathname);
        });

        // --- VIEW MODE SWITCHER LOGIC ---
        function switchView(mode) {
            const stats = document.getElementById('statsSection');
            const tableCol = document.getElementById('tableCol');
            const chartCol = document.getElementById('chartCol');
            const title = document.getElementById('pageTitle');
            
            // Reset Classes
            document.querySelectorAll('.btn-mode').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('active'));

            if (mode === 'live') {
                // LIVE DATA MODE (Table Full Screen)
                stats.classList.add('hidden-section');
                chartCol.classList.add('hidden-section');
                
                tableCol.classList.remove('hidden-section', 'col-xl-8');
                tableCol.classList.add('col-12', 'fade-in');
                
                title.innerText = "Live Data View";
                document.getElementById('btn-inv').classList.add('active');
                document.getElementById('nav-inv').classList.add('active');
            } 
            else if (mode === 'report') {
                // REPORT MODE (Chart Full Screen)
                stats.classList.add('hidden-section');
                tableCol.classList.add('hidden-section');
                
                chartCol.classList.remove('hidden-section', 'col-xl-4');
                chartCol.classList.add('col-12', 'fade-in');
                
                title.innerText = "Analytics Report";
                document.getElementById('btn-rep').classList.add('active');
                document.getElementById('nav-rep').classList.add('active');
            } 
            else {
                // DASHBOARD MODE (Default Split)
                stats.classList.remove('hidden-section');
                tableCol.classList.remove('hidden-section', 'col-12');
                chartCol.classList.remove('hidden-section', 'col-12');
                
                tableCol.classList.add('col-xl-8', 'fade-in');
                chartCol.classList.add('col-xl-4', 'fade-in');
                
                title.innerText = "Dashboard Overview";
                document.getElementById('btn-dash').classList.add('active');
                document.getElementById('nav-dash').classList.add('active');
            }
            
            // Resize chart just in case
            if (window.myChart) window.myChart.resize();
        }

        // --- CHART CONFIG ---
        const ctx = document.getElementById('smartChart').getContext('2d');
        const labels = <?= json_encode($labels) ?>;
        const dataValues = <?= json_encode($stokData) ?>;

        function getGradient(ctx, value) {
            let gradient = ctx.createLinearGradient(0, 0, 300, 0);
            if (value < 5) {
                gradient.addColorStop(0, '#f43f5e'); gradient.addColorStop(1, 'rgba(244, 63, 94, 0.1)');
            } else {
                gradient.addColorStop(0, '#06b6d4'); gradient.addColorStop(1, 'rgba(99, 102, 241, 0.1)');
            }
            return gradient;
        }

        const borderColors = dataValues.map(v => v < 5 ? '#f43f5e' : '#22d3ee');

        window.myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Units',
                    data: dataValues,
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        return getGradient(ctx, context.raw);
                    },
                    borderColor: borderColors,
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: { label: function(context) { return context.raw + ' Units ' + (context.raw < 5 ? '⚠️' : '✅'); } }
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(255, 255, 255, 0.05)', borderDash: [4, 4] }, ticks: { color: '#64748b' } },
                    y: { grid: { display: false }, ticks: { color: '#e2e8f0', font: { size: 11, family: "'Outfit', sans-serif" } } }
                }
            }
        });
    </script>
</body>
</html>