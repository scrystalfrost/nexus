<?php
include 'db_connect.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$isEdit = !empty($id);
$d = ['Nama_barang'=>'', 'Deskripsi_barang'=>'', 'Harga_barang'=>'', 'Stok_barang'=>''];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM Tb_Barang WHERE Id_barang = ?");
    $stmt->execute([$id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res) $d = $res;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title><?= $isEdit ? 'Update Module' : 'Input Module' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #020617; 
            --card-dark: #0f172a; 
            --text-light: #f1f5f9; --text-gray: #94a3b8;
            --primary: #6366f1; --accent: #8b5cf6; 
            --glow: 0 0 25px rgba(99, 102, 241, 0.25);
        }

        body { font-family: 'Outfit', sans-serif; background: var(--bg-dark); height: 100vh; overflow: hidden; color: var(--text-light); }
        
        /* Layout Split */
        .split-layout { height: 100%; display: flex; }
        
        /* Left Side - Visual */
        .visual-side {
            flex: 1; position: relative; overflow: hidden;
            background: radial-gradient(circle at center, #1e1b4b 0%, #020617 100%);
            display: flex; flex-direction: column; justify-content: center; padding: 80px;
        }
        
        /* Animated Background Elements */
        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.6;
            animation: float 10s infinite ease-in-out;
        }
        .orb-1 { width: 300px; height: 300px; background: var(--primary); top: -50px; left: -50px; animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; background: var(--accent); bottom: -100px; right: -100px; animation-delay: -5s; }
        
        @keyframes float { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(30px, 50px); } }
        
        .glass-panel {
            position: relative; z-index: 10;
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 30px;
            padding: 50px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Right Side - Form */
        .form-side {
            flex: 0 0 600px; background: var(--bg-dark); 
            display: flex; align-items: center; justify-content: center;
            border-left: 1px solid rgba(255,255,255,0.05);
            position: relative; overflow-y: auto;
        }
        
        .form-container { width: 100%; max-width: 480px; padding: 40px; position: relative; z-index: 2; }

        /* Typography */
        h1 { font-weight: 700; letter-spacing: -1px; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        h3 { font-weight: 600; letter-spacing: -0.5px; }

        /* Inputs */
        .form-label { 
            color: var(--text-gray); font-size: 0.75rem; font-weight: 700; 
            text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; 
            display: block;
        }
        
        .input-wrapper { position: relative; margin-bottom: 25px; }
        
        .form-control {
            background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1);
            color: white; padding: 15px 20px; border-radius: 16px; transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus {
            background: rgba(15, 23, 42, 0.9); border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15), 0 0 20px rgba(99, 102, 241, 0.1);
            color: white; outline: none;
        }
        
        .form-control::placeholder { color: rgba(148, 163, 184, 0.5); }
        
        .input-icon {
            position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
            color: var(--text-gray); opacity: 0.5; transition: 0.3s; pointer-events: none;
        }
        .form-control:focus + .input-icon { color: var(--primary); opacity: 1; }

        /* Button */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white; width: 100%; padding: 18px; border-radius: 50px;
            border: none; font-weight: 600; font-size: 1rem; letter-spacing: 0.5px;
            margin-top: 20px; cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3); position: relative; overflow: hidden;
        }
        
        .btn-submit::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-submit:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5); 
        }
        .btn-submit:hover::before { left: 100%; }

        /* Back Button */
        .btn-back {
            display: inline-flex; align-items: center; gap: 10px;
            color: var(--text-gray); text-decoration: none; font-weight: 500;
            margin-bottom: 40px; transition: 0.3s; padding: 10px 20px;
            border-radius: 30px; border: 1px solid transparent;
        }
        .btn-back:hover { 
            color: white; background: rgba(255,255,255,0.05); 
            border-color: rgba(255,255,255,0.1); transform: translateX(-5px); 
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .visual-side { display: none; }
            .form-side { flex: 1; border-left: none; }
        }
    </style>
</head>
<body>

<div class="split-layout">
    
    <div class="visual-side">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        
        <div class="glass-panel animate__animated animate__fadeIn">
            <h1 class="display-4 mb-3"><?= $isEdit ? 'System<br>Update.' : 'System<br>Entry.' ?></h1>
            <p class="lead text-gray mb-4" style="line-height: 1.8;">
                <?= $isEdit 
                    ? 'Modifying secure asset parameters. Ensure all calibration data matches physical inventory before committing changes.' 
                    : 'Initializing new asset sequence. Please provide accurate specifications for the holographic database.' ?>
            </p>
            
            <div class="d-flex gap-3 mt-4">
                <div class="px-3 py-2 rounded-3 border border-white border-opacity-10 bg-white bg-opacity-5">
                    <small class="text-uppercase text-gray" style="font-size:0.65rem; letter-spacing:1px;">Security</small>
                    <div class="fw-bold text-success"><i class="fas fa-lock me-1"></i> Encrypted</div>
                </div>
                <div class="px-3 py-2 rounded-3 border border-white border-opacity-10 bg-white bg-opacity-5">
                    <small class="text-uppercase text-gray" style="font-size:0.65rem; letter-spacing:1px;">Database</small>
                    <div class="fw-bold text-info"><i class="fas fa-database me-1"></i> Connected</div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-side">
        <div class="form-container">
            <a href="list_barang.php" class="btn-back">
                <i class="fas fa-chevron-left"></i> <span>Dashboard</span>
            </a>

            <div class="mb-5">
                <h3 class="mb-2 text-white"><?= $isEdit ? 'Modify Asset' : 'Register Asset' ?></h3>
                <p class="text-gray small">Fill in the technical specifications below.</p>
            </div>

            <form action="proses_update.php" method="POST">
                <input type="hidden" name="id_barang" value="<?= $id ?>">

                <div class="input-wrapper">
                    <label class="form-label">Item Designation</label>
                    <input type="text" class="form-control" name="nama_barang" value="<?= htmlspecialchars($d['Nama_barang']) ?>" placeholder="e.g. Quantum Processor Unit" required>
                    <i class="fas fa-tag input-icon"></i>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="input-wrapper">
                            <label class="form-label">Value (IDR)</label>
                            <input type="number" step="0.01" class="form-control" name="harga_barang" value="<?= $d['Harga_barang'] ?>" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-wrapper">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="stok_barang" value="<?= $d['Stok_barang'] ?>" placeholder="0" required>
                        </div>
                    </div>
                </div>

                <div class="input-wrapper">
                    <label class="form-label">Technical Specs</label>
                    <textarea class="form-control" name="deskripsi_barang" rows="4" placeholder="Enter detailed specifications..."><?= htmlspecialchars($d['Deskripsi_barang']) ?></textarea>
                    <i class="fas fa-file-alt input-icon" style="top: 30px;"></i>
                </div>

                <button type="submit" class="btn-submit">
                    <?= $isEdit ? 'INITIATE UPDATE SEQUENCE' : 'CONFIRM ENTRY' ?> <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>
        </div>
    </div>
    
</div>

</body>
</html>