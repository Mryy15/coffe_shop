<?php
session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include "config_db.php";

// Tanggal hari ini
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$this_month = date('Y-m');

// ========== STATISTIK UTAMA ==========
// Total pesanan hari ini
$sql_today = "SELECT COUNT(*) as total, SUM(total_amount) as revenue 
              FROM orders WHERE DATE(order_date) = '$today'";
$result_today = mysqli_query($conn, $sql_today);
$today_stats = mysqli_fetch_assoc($result_today);

// Total pesanan bulan ini
$sql_month = "SELECT COUNT(*) as total, SUM(total_amount) as revenue 
              FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = '$this_month'";
$result_month = mysqli_query($conn, $sql_month);
$month_stats = mysqli_fetch_assoc($result_month);

// Total semua pesanan
$sql_all = "SELECT COUNT(*) as total_orders, COUNT(DISTINCT user_id) as total_customers,
            SUM(total_amount) as total_revenue, AVG(total_amount) as avg_order_value
            FROM orders";
$result_all = mysqli_query($conn, $sql_all);
$all_stats = mysqli_fetch_assoc($result_all);

// Pesanan pending (butuh perhatian)
$sql_pending = "SELECT COUNT(*) as pending_count FROM orders WHERE status IN ('pending', 'preparing')";
$result_pending = mysqli_query($conn, $sql_pending);
$pending_stats = mysqli_fetch_assoc($result_pending);

// ========== DATA UNTUK CHART ==========
// Data 7 hari terakhir untuk chart
$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime($date));
    $chart_labels[] = "$day_name<br>" . date('d/m', strtotime($date));
    
    $sql_day = "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = '$date'";
    $result_day = mysqli_query($conn, $sql_day);
    $day_data = mysqli_fetch_assoc($result_day);
    $chart_data[] = $day_data['count'] ?? 0;
}

// ========== PESANAN TERBARU ==========
$sql_recent = "SELECT o.*, u.username 
               FROM orders o 
               LEFT JOIN users u ON o.user_id = u.id 
               ORDER BY o.order_date DESC 
               LIMIT 10";
$result_recent = mysqli_query($conn, $sql_recent);

// ========== PRODUK TERLARIS ==========
$sql_best = "SELECT menu_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue
             FROM order_items 
             GROUP BY menu_name 
             ORDER BY total_qty DESC 
             LIMIT 5";
$result_best = mysqli_query($conn, $sql_best);

// ========== PELANGGAN AKTIF ==========
$sql_customers = "SELECT u.username, u.email, 
                  COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent
                  FROM users u
                  LEFT JOIN orders o ON u.id = o.user_id
                  WHERE u.role = 'user'
                  GROUP BY u.id
                  ORDER BY order_count DESC
                  LIMIT 5";
$result_customers = mysqli_query($conn, $sql_customers);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Coffee Shop</title>
    <!-- Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --coffee: #7a4a2e;
            --admin-blue: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #3498db;
            --light-bg: #f8f9fa;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            color: #333;
            min-height: 100vh;
        }
        
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        /* ===== SIDEBAR ===== */
        .sidebar {
            background: var(--admin-blue);
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .admin-logo h1 {
            font-family: 'Dancing Script', cursive;
            font-size: 2rem;
            color: white;
        }
        
        .admin-logo p {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        .admin-info {
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            margin: 0 15px 20px;
            border-radius: 10px;
        }
        
        .admin-avatar {
            width: 60px;
            height: 60px;
            background: white;
            color: var(--admin-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 10px;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            padding: 12px 25px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: 0.3s;
            cursor: pointer;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .nav-item.active {
            background: var(--coffee);
        }
        
        .nav-item a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-icon {
            width: 20px;
            text-align: center;
        }
        
        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            margin: 0 20px;
            background: rgba(231, 76, 60, 0.8);
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            transition: 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            padding: 20px;
            overflow-y: auto;
        }
        
        .header-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title h2 {
            color: var(--admin-blue);
            margin-bottom: 5px;
        }
        
        .header-stats {
            display: flex;
            gap: 15px;
        }
        
        .stat-badge {
            background: var(--light-bg);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .stat-badge.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        /* ===== STATS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .icon-orders { background: var(--info); }
        .icon-revenue { background: var(--success); }
        .icon-customers { background: var(--warning); }
        .icon-pending { background: var(--danger); }
        
        .stat-content h3 {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--admin-blue);
            margin-bottom: 5px;
        }
        
        .stat-change {
            font-size: 12px;
            color: #27ae60;
        }
        
        .stat-change.negative {
            color: #e74c3c;
        }
        
        /* ===== CHARTS & TABLES ===== */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-container, .table-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .chart-container h3, .table-container h3 {
            color: var(--admin-blue);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* ===== TABLES ===== */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .admin-table th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            color: var(--admin-blue);
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }
        
        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .admin-table tr:hover {
            background: #f8f9fa;
        }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-preparing { background: #cce5ff; color: #004085; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        
        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        /* ===== QUICK ACTIONS ===== */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: var(--coffee);
            color: white;
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--coffee);
        }
        
        .action-card:hover .action-icon {
            color: white;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                height: auto;
                position: relative;
            }
            
            .logout-btn {
                position: relative;
                margin: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
<div class="sidebar">
    <div class="admin-logo">
        <h1>Coffee Shop</h1>
        <p>Admin Dashboard</p>
    </div>
    
    <div class="system-info" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); margin: 0 15px 20px; border-radius: 10px;">
        <div style="font-size: 2rem; margin-bottom: 10px;">☕</div>
        <h4 style="margin: 0 0 5px 0;">System Status</h4>
        <div style="display: flex; justify-content: center; gap: 10px; margin: 10px 0;">
            <span style="background: #27ae60; padding: 3px 8px; border-radius: 10px; font-size: 11px;">Online</span>
            <span style="background: #3498db; padding: 3px 8px; border-radius: 10px; font-size: 11px;"><?php echo date('H:i'); ?></span>
        </div>
        <small style="opacity: 0.8;">Admin Panel v1.0</small>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item active">
            <a href="admin_dashboard.php">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_orders.php">
                <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>Semua Pesanan</span>
                <?php if($pending_stats['pending_count'] > 0): ?>
                <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                    <?php echo $pending_stats['pending_count']; ?>
                </span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_fix.php">
                <span class="nav-icon"><i class="fas fa-utensils"></i></span>
                <span>Kelola Menu</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_customers.php">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span>Pelanggan</span>
                <span style="background: #3498db; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                    <?php echo $all_stats['total_customers'] ?? 0; ?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_reports.php">
                <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                <span>Laporan</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_settings.php">
                <span class="nav-icon"><i class="fas fa-cog"></i></span>
                <span>Pengaturan</span>
            </a>
        </li>
    </ul>
    
    <!-- USER INFO DI BAWAH -->
    <div style="position: absolute; bottom: 80px; left: 0; right: 0; padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); margin: 0 15px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 40px; height: 40px; background: white; color: var(--admin-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600;"><?php echo $_SESSION['username']; ?></div>
                <small style="opacity: 0.8;">Administrator</small>
            </div>
        </div>
    </div>
    
    <a href="index.php?logout=true" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>
        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- HEADER -->
            <div class="header-bar">
                <div class="header-title">
                    <h2>Admin Dashboard</h2>
                    <p>Ringkasan kinerja Coffee Shop</p>
                </div>
                <div class="header-stats">
                    <div class="stat-badge">
                        <i class="fas fa-calendar-day"></i> <?php echo date('d F Y'); ?>
                    </div>
                    <div class="stat-badge warning">
                        <i class="fas fa-clock"></i> <?php echo $pending_stats['pending_count']; ?> pesanan butuh perhatian
                    </div>
                </div>
            </div>
            
            <!-- STATISTIK UTAMA -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pesanan Hari Ini</h3>
                        <div class="stat-number"><?php echo $today_stats['total'] ?? 0; ?></div>
                        <div class="stat-change">
                            <i class="fas fa-coins"></i> Rp <?php echo number_format($today_stats['revenue'] ?? 0, 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon icon-revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pendapatan Bulan Ini</h3>
                        <div class="stat-number">Rp <?php echo number_format($month_stats['revenue'] ?? 0, 0, ',', '.'); ?></div>
                        <div class="stat-change">
                            <?php echo $month_stats['total'] ?? 0; ?> transaksi
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon icon-customers">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Pelanggan</h3>
                        <div class="stat-number"><?php echo $all_stats['total_customers'] ?? 0; ?></div>
                        <div class="stat-change">
                            <?php echo $all_stats['total_orders'] ?? 0; ?> total pesanan
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon icon-pending">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pesanan Pending</h3>
                        <div class="stat-number"><?php echo $pending_stats['pending_count'] ?? 0; ?></div>
                        <div class="stat-change negative">
                            Perlu diproses segera
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- CHART & TABLES -->
            <div class="content-grid">
                <!-- CHART PESANAN 7 HARI -->
                <div class="chart-container">
                    <h3><i class="fas fa-chart-line"></i> Pesanan 7 Hari Terakhir</h3>
                    <canvas id="ordersChart" height="250"></canvas>
                </div>
                
                <!-- PRODUK TERLARIS -->
                <div class="table-container">
                    <h3><i class="fas fa-star"></i> Produk Terlaris</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Terjual</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result_best && mysqli_num_rows($result_best) > 0): ?>
                                <?php while($product = mysqli_fetch_assoc($result_best)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['menu_name']); ?></td>
                                    <td><span class="badge badge-success"><?php echo $product['total_qty']; ?></span></td>
                                    <td>Rp <?php echo number_format($product['total_revenue'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px; color: #666;">
                                        Belum ada data penjualan
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- TABEL PESANAN TERBARU -->
            <div class="table-container">
                <h3><i class="fas fa-history"></i> Pesanan Terbaru</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tipe</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result_recent && mysqli_num_rows($result_recent) > 0): ?>
                            <?php while($order = mysqli_fetch_assoc($result_recent)): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $order['order_number']; ?></strong><br>
                                    <small style="color: #666;"><?php echo $order['payment_method'] == 'cash' ? 'Tunai' : 'QRIS'; ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                    <small style="color: #666;">@<?php echo $order['username'] ?? 'Guest'; ?></small>
                                </td>
                                <td><?php echo $order['order_type'] == 'dine_in' ? 'Dine In' : 'Takeaway'; ?></td>
                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td>
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
                                </td>
                                <td><?php echo date('H:i', strtotime($order['order_date'])); ?><br>
                                    <small><?php echo date('d/m', strtotime($order['order_date'])); ?></small>
                                </td>
                                <td>
                                    <a href="admin_orders.php?view=<?php echo $order['id']; ?>" 
                                       style="color: var(--info); text-decoration: none;">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                    Belum ada pesanan
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- QUICK ACTIONS -->
            <div class="quick-actions">
                <a href="admin_orders.php?status=pending" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4>Kelola Pesanan Pending</h4>
                    <p><?php echo $pending_stats['pending_count']; ?> pesanan menunggu</p>
                </a>
                
                <a href="admin_fix.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h4>Tambah Menu Baru</h4>
                    <p>Perbarui daftar menu</p>
                </a>
                
                <a href="admin_reports.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h4>Buat Laporan</h4>
                    <p>Generate laporan harian/bulanan</p>
                </a>
                
                <a href="index.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h4>Lihat Toko</h4>
                    <p>Pratinjau halaman utama</p>
                </a>
            </div>
        </div>
    </div>
    
    <!-- CHART SCRIPT -->
    <script>
        // Data untuk chart
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const chartData = <?php echo json_encode($chart_data); ?>;
        
        // Buat chart
        const ctx = document.getElementById('ordersChart').getContext('2d');
        const ordersChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: chartData,
                    backgroundColor: 'rgba(122, 74, 46, 0.1)',
                    borderColor: 'rgba(122, 74, 46, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Pesanan: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });
        
        // Update jam real-time
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        
        // Update setiap detik
        setInterval(updateClock, 1000);
        updateClock();
        
        // Notifikasi untuk pesanan baru (simulasi)
        function checkNewOrders() {
            fetch('check_new_orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.new_orders > 0) {
                        showNotification(`Ada ${data.new_orders} pesanan baru!`);
                    }
                });
        }
        
        // Cek setiap 30 detik
        setInterval(checkNewOrders, 30000);
        
        function showNotification(message) {
            // Buat notifikasi sederhana
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--coffee);
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-bell"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Hapus setelah 5 detik
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    </script>
    
    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    </style>
</body>
</html>