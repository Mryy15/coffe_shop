<?php
session_start();

// DEBUG: Tampilkan session data
echo "<!-- ADMIN DEBUG SESSION: ";
print_r($_SESSION);
echo " -->";

// CEK APAKAH USER SUDAH LOGIN DAN BERPERAN ADMIN
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo "<h1 style='color: red;'>AKSES DITOLAK!</h1>";
    echo "<p>Anda harus login sebagai admin untuk mengakses halaman ini.</p>";
    echo "<p>Session data: ";
    print_r($_SESSION);
    echo "</p>";
    echo "<p><a href='index.php'>Kembali ke Home</a> | ";
    echo "<a href='index.php?login=admin'>Login sebagai Admin</a></p>";
    exit();
}

// PROSES LOGOUT
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Coffee Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f5f5f5;
            color: #333;
        }
        
        .admin-header {
            background: #7a4a2e;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            font-family: 'Dancing Script', cursive;
            font-size: 2rem;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            background: rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        
        .admin-nav a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .admin-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.12);
        }
        
        .admin-card h3 {
            color: #7a4a2e;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .admin-card ul {
            list-style: none;
            padding-left: 0;
        }
        
        .admin-card li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .btn-admin {
            display: inline-block;
            padding: 10px 20px;
            background: #7a4a2e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 15px;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .btn-admin:hover {
            background: #5a3a22;
        }
        
        .logout-btn {
            background: #e74c3c;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .user-info {
            font-size: 0.9rem;
            color: #777;
            margin-top: 10px;
        }
        
        /* DEBUG INFO */
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- DEBUG INFO -->
    <div class="debug-info">
        <strong>DEBUG INFO:</strong> 
        User ID: <?php echo $_SESSION['user_id'] ?? 'null'; ?> | 
        Username: <?php echo $_SESSION['username'] ?? 'null'; ?> | 
        Role: <?php echo $_SESSION['role'] ?? 'null'; ?>
    </div>
    
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <div class="admin-nav">
            <a href="admin_menu.php">Dashboard</a>
            <a href="#" onclick="alert('Fitur akan datang!')">Produk</a>
            <a href="#" onclick="alert('Fitur akan datang!')">Pesanan</a>
            <a href="#" onclick="alert('Fitur akan datang!')">Pengguna</a>
            <a href="index.php?logout=true" class="logout-btn">Logout</a>
        </div>
    </header>
    
    <div class="admin-container">
        <div class="welcome-box">
            <h2>🎉 Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h2>
            <p class="user-info">Anda login sebagai <strong style="color: #e74c3c;">Administrator</strong> | <?php echo date('d F Y H:i:s'); ?></p>
        </div>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h3>📊 Statistik</h3>
                <ul>
                    <li>Total Produk: <strong>12</strong></li>
                    <li>Pesanan Hari Ini: <strong>8</strong></li>
                    <li>Pengguna Terdaftar: <strong>45</strong></li>
                    <li>Pendapatan Bulan Ini: <strong>Rp 12.500.000</strong></li>
                </ul>
            </div>
            
            <div class="admin-card">
                <h3>⚡ Aksi Cepat</h3>
                <ul>
                    <li><a href="#" class="btn-admin" onclick="alert('Fitur Tambah Produk akan datang!')">Tambah Produk Baru</a></li>
                    <li><a href="#" class="btn-admin" onclick="alert('Fitur Lihat Pesanan akan datang!')">Lihat Pesanan</a></li>
                    <li><a href="#" class="btn-admin" onclick="alert('Fitur Kelola Pengguna akan datang!')">Kelola Pengguna</a></li>
                    <li><a href="#" class="btn-admin" onclick="alert('Fitur Pengaturan akan datang!')">Pengaturan</a></li>
                </ul>
            </div>
            
            <div class="admin-card">
                <h3>📈 Aktivitas Terbaru</h3>
                <ul>
                    <li>Pesanan baru dari <strong>Budi</strong> (5 menit lalu)</li>
                    <li>Produk <strong>Latte Coconut</strong> diperbarui</li>
                    <li>User <strong>Sari</strong> baru mendaftar</li>
                    <li>Review baru diterima</li>
                </ul>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php" class="btn-admin">← Kembali ke Website</a>
            <a href="index.php?logout=true" class="btn-admin logout-btn">Logout</a>
        </div>
    </div>
</body>
</html>