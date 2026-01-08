<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include "config_db.php";

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    // Jika data tidak ditemukan, gunakan data dari session
    $user = [
        'username' => $_SESSION['username'] ?? 'User',
        'email' => $_SESSION['email'] ?? '',
        'phone' => $_SESSION['phone'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Ambil riwayat pesanan
$order_sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC LIMIT 5";
$order_result = mysqli_query($conn, $order_sql);

// Hitung statistik
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_spent,
    MAX(order_date) as last_order
    FROM orders WHERE user_id = '$user_id'";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member - Coffee Shop</title>
    <style>
        :root {
            --coffee: #7a4a2e;
            --soft-sand: #f7efe5;
            --light-cream: #fffaf0;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--soft-sand), var(--light-cream));
            color: #333;
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .logo {
            font-family: 'Dancing Script', cursive;
            font-size: 2.5rem;
            color: var(--coffee);
            text-decoration: none;
        }
        
        .user-welcome h1 {
            color: var(--coffee);
            margin-bottom: 5px;
            font-size: 1.8rem;
        }
        
        .user-role {
            background: #e8f4fc;
            color: #2c3e50;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 14px;
            display: inline-block;
            margin-top: 5px;
        }
        
        .user-role.admin {
            background: #ffeaa7;
            color: #d35400;
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
        }
        
        .nav-btn {
            padding: 10px 20px;
            background: var(--coffee);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .nav-btn:hover {
            background: #5a3822;
            transform: translateY(-2px);
        }
        
        .nav-btn.outline {
            background: transparent;
            color: var(--coffee);
            border: 2px solid var(--coffee);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card h2 {
            color: var(--coffee);
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--coffee);
            margin: 5px 0;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: 0.3s;
        }
        
        .action-btn:hover {
            background: var(--coffee);
            color: white;
            transform: translateY(-5px);
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .order-list {
            list-style: none;
        }
        
        .order-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-number {
            font-weight: 600;
            color: var(--coffee);
        }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-preparing { background: #cce5ff; color: #004085; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        
        .empty-orders {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            transition: 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- HEADER -->
        <div class="header">
            <a href="index.php" class="logo">Coffee Shop</a>
            
            <div class="user-welcome">
                <h1>Halo, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p>Selamat datang di area member Coffee Shop</p>
                <span class="user-role <?php echo ($_SESSION['role'] ?? 'user') === 'admin' ? 'admin' : ''; ?>">
                    <?php echo ($_SESSION['role'] ?? 'user') === 'admin' ? 'Administrator' : 'Member'; ?>
                </span>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="nav-btn">Beranda</a>
                <a href="menu.php" class="nav-btn">Menu</a>
                <a href="index.php?logout=true" class="nav-btn outline">Logout</a>
            </div>
        </div>
        
        <!-- MAIN CONTENT -->
        <div class="content-grid">
            <!-- KOLOM KIRI: Info Akun & Statistik -->
            <div>
                <!-- INFO AKUN -->
                <div class="card">
                    <h2>📋 Informasi Akun</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Username:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email'] ?? 'Belum diisi'); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Nomor Telepon:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Belum diisi'); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Member Sejak:</span>
                            <span class="info-value">
                                <?php 
                                if (isset($user['created_at'])) {
                                    echo date('d F Y', strtotime($user['created_at']));
                                } else {
                                    echo 'Hari ini';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Role:</span>
                            <span class="info-value">
                                <strong><?php echo ($_SESSION['role'] ?? 'user') === 'admin' ? 'Administrator' : 'Member'; ?></strong>
                            </span>
                        </div>
                    </div>
                    
                    <!-- STATISTIK -->
                    <h2 style="margin-top: 30px;">📊 Statistik Anda</h2>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['total_orders'] ?? 0; ?></div>
                            <div class="stat-label">Total Pesanan</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">Rp <?php echo number_format($stats['total_spent'] ?? 0, 0, ',', '.'); ?></div>
                            <div class="stat-label">Total Belanja</div>
                        </div>
                    </div>
                </div>
                
                <!-- MENU CEPAT -->
                <div class="card" style="margin-top: 30px;">
                    <h2>⚡ Menu Cepat</h2>
                    <p style="margin-bottom: 20px; color: #666;">Akses cepat ke fitur Coffee Shop:</p>
                    
                    <div class="quick-actions">
                        <a href="menu.php" class="action-btn">
                            <div class="action-icon">☕</div>
                            <span>Lihat Menu</span>
                        </a>
                        
                        <a href="#contact" class="action-btn">
                            <div class="action-icon">📅</div>
                            <span>Reservasi</span>
                        </a>
                        
                        <a href="cart.php" class="action-btn">
                            <div class="action-icon">🛒</div>
                            <span>Keranjang</span>
                        </a>
                        
                        <a href="my_orders.php" class="action-btn">
                            <div class="action-icon">📋</div>
                            <span>Pesanan Saya</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- KOLOM KANAN: Riwayat Pesanan -->
            <div>
                <!-- RIWAYAT PESANAN -->
                <div class="card">
                    <h2>📦 Riwayat Pesanan Terakhir</h2>
                    
                    <?php if($order_result && mysqli_num_rows($order_result) > 0): ?>
                        <ul class="order-list">
                            <?php while($order = mysqli_fetch_assoc($order_result)): ?>
                            <li class="order-item">
                                <div>
                                    <div class="order-number"><?php echo $order['order_number']; ?></div>
                                    <div style="font-size: 14px; color: #666;">
                                        <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?> • 
                                        <?php echo $order['order_type'] == 'dine_in' ? 'Dine In' : 'Takeaway'; ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: bold; color: var(--coffee);">
                                        Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </div>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $status_text = [
                                            'pending' => 'Menunggu',
                                            'preparing' => 'Diproses',
                                            'ready' => 'Siap',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Batal'
                                        ];
                                        echo $status_text[$order['status']] ?? $order['status'];
                                        ?>
                                    </span>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <a href="my_orders.php" class="nav-btn">Lihat Semua Pesanan</a>
                        </div>
                    <?php else: ?>
                        <div class="empty-orders">
                            <p style="font-size: 5rem; margin-bottom: 20px;">🛒</p>
                            <p>Belum ada pesanan.</p>
                            <p style="font-size: 14px; margin-top: 10px;">Yuk, pesan kopi favoritmu sekarang!</p>
                            <a href="menu.php" class="nav-btn" style="margin-top: 20px;">Pesan Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- TIPS & INFO -->
                <div class="card" style="margin-top: 30px; background: #f0f7ff;">
                    <h2 style="color: #2c3e50;">💡 Tips & Info</h2>
                    <ul style="list-style: none; padding-left: 0;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>🕐 Jam Operasional:</strong> 08:00 - 22:00 WIB
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>📍 Lokasi:</strong> Jl. Pantai Indah No. 123
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>📞 Reservasi:</strong> (021) 1234-5678
                        </li>
                        <li style="padding: 10px 0;">
                            <strong>🎁 Promo:</strong> Diskon 10% untuk setiap pembelian ke-5
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- LOGOUT -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php?logout=true" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout dari Akun
            </a>
        </div>
        
        <footer>
            <p>&copy; 2025 Coffee Shop. Hak cipta dilindungi.</p>
            <p style="font-size: 12px; margin-top: 5px;">Dashboard Member v1.0</p>
        </footer>
    </div>
</body>
</html>