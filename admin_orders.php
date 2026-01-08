<?php
// admin_orders.php
session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include "config_db.php";

// Fungsi update status pesanan
if (isset($_GET['update_status'])) {
    $order_id = intval($_GET['update_status']);
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);
    
    $sql = "UPDATE orders SET status = '$new_status' WHERE id = '$order_id'";
    mysqli_query($conn, $sql);
    
    header("Location: admin_orders.php");
    exit();
}

// Ambil semua pesanan
$sql = "SELECT o.*, u.username 
        FROM orders o 
        LEFT JOIN (SELECT id as user_id, username FROM users) u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pesanan</title>
    <style>
        :root {
            --coffee: #7a4a2e;
            --soft-sand: #f7efe5;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--soft-sand);
            color: #333;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .admin-title {
            color: var(--coffee);
            font-family: 'Dancing Script', cursive;
            font-size: 2.5rem;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        
        .admin-btn {
            padding: 10px 20px;
            background: var(--coffee);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .admin-btn:hover {
            background: #5a3822;
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .order-table th, .order-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-table th {
            background: #f8f8f8;
            color: var(--coffee);
            font-weight: 600;
        }
        
        .order-table tr:hover {
            background: #f9f9f9;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-preparing { background: #cce5ff; color: #004085; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-status {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-view {
            background: #3498db;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--coffee);
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .order-details {
            display: none;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .order-details.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Admin Panel - Pesanan</h1>
            <div class="admin-nav">
                <a href="admin_fix.php" class="admin-btn">Kelola Menu</a>
                <a href="admin_orders.php" class="admin-btn" style="background:#e74c3c;">Pesanan</a>
                <a href="index.php" class="admin-btn">Kembali ke Home</a>
            </div>
        </div>
        
        <!-- Statistik -->
        <div class="stats-container">
            <?php
            // Query statistik
            $stats_sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
                SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(total_amount) as total_revenue
                FROM orders";
            $stats_result = mysqli_query($conn, $stats_sql);
            $stats = mysqli_fetch_assoc($stats_result);
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['preparing']; ?></div>
                <div class="stat-label">Sedang Diproses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
        
        <!-- Tabel Pesanan -->
        <h2>Daftar Pesanan</h2>
        <table class="order-table">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Tipe</th>
                    <th>Pembayaran</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && mysqli_num_rows($result) > 0): 
                    while($order = mysqli_fetch_assoc($result)):
                ?>
                <tr>
                    <td>
                        <strong><?php echo $order['order_number']; ?></strong>
                        <br>
                        <small><a href="#" onclick="toggleDetails(<?php echo $order['id']; ?>)">Lihat detail</a></small>
                    </td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?>
                        <br><small><?php echo $order['username'] ?? 'Guest'; ?></small>
                    </td>
                    <td><?php echo $order['order_type'] == 'dine_in' ? 'Dine In' : 'Takeaway'; ?></td>
                    <td><?php echo $order['payment_method'] == 'cash' ? 'Tunai' : 'QRIS'; ?></td>
                    <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php 
                                $status_text = [
                                    'pending' => 'Menunggu',
                                    'preparing' => 'Diproses',
                                    'ready' => 'Siap',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Batal'
                                ];
                                echo $status_text[$order['status']];
                            ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                    <td class="action-buttons">
                        <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)" 
                                style="padding:5px; border-radius:5px; border:1px solid #ddd;">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>Diproses</option>
                            <option value="ready" <?php echo $order['status'] == 'ready' ? 'selected' : ''; ?>>Siap</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Batal</option>
                        </select>
                    </td>
                </tr>
                <!-- Detail pesanan -->
                <tr id="details-<?php echo $order['id']; ?>" class="order-details">
                    <td colspan="8">
                        <h4>Detail Pesanan:</h4>
                        <?php
                        // Ambil item pesanan
                        $order_id = $order['id'];
                        $items_sql = "SELECT * FROM order_items WHERE order_id = '$order_id'";
                        $items_result = mysqli_query($conn, $items_sql);
                        
                        if ($items_result && mysqli_num_rows($items_result) > 0):
                        ?>
                        <table style="width:100%; background:white; border-radius:8px; padding:10px;">
                            <tr style="background:#f0f0f0;">
                                <th style="padding:8px;">Menu</th>
                                <th style="padding:8px;">Jumlah</th>
                                <th style="padding:8px;">Harga</th>
                                <th style="padding:8px;">Subtotal</th>
                            </tr>
                            <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                            <tr>
                                <td style="padding:8px;"><?php echo $item['menu_name']; ?></td>
                                <td style="padding:8px;"><?php echo $item['quantity']; ?></td>
                                <td style="padding:8px;">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td style="padding:8px;">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                        <?php endif; ?>
                        
                        <?php if(!empty($order['notes'])): ?>
                        <p style="margin-top:10px;"><strong>Catatan:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px;">
                        Belum ada pesanan.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        function updateOrderStatus(orderId, newStatus) {
            if (confirm("Update status pesanan ini?")) {
                window.location.href = `admin_orders.php?update_status=${orderId}&status=${newStatus}`;
            }
        }
        
        function toggleDetails(orderId) {
            event.preventDefault();
            const detailsRow = document.getElementById(`details-${orderId}`);
            detailsRow.classList.toggle('show');
        }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>