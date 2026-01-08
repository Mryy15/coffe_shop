<?php
session_start();
include "config_db.php";

// CEK APAKAH ADMIN
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// PROSES DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM menu_items WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_msg = "Produk berhasil dihapus!";
    } else {
        $error_msg = "Gagal menghapus produk: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// PROSES TOGGLE ACTIVE
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = mysqli_prepare($conn, "UPDATE menu_items SET is_active = NOT is_active WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_msg = "Status produk berhasil diubah!";
    } else {
        $error_msg = "Gagal mengubah status: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// AMBIL DATA MENU
$sql = "SELECT * FROM menu_items ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$menu_items = [];

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $menu_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Menu - Admin Panel</title>
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
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #7a4a2e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .btn:hover {
            background: #5a3a22;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #219653;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-warning {
            background: #f39c12;
        }
        
        .btn-warning:hover {
            background: #d68910;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .messages {
            margin: 20px 0;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            margin-bottom: 15px;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin-bottom: 15px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #7a4a2e;
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
        }
        
        .status-active {
            display: inline-block;
            padding: 5px 10px;
            background: #27ae60;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-inactive {
            display: inline-block;
            padding: 5px 10px;
            background: #95a5a6;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .category-badge {
            display: inline-block;
            padding: 5px 10px;
            background: #eef6fb;
            border-radius: 20px;
            font-size: 12px;
            color: #555;
        }
        
        .price {
            color: #7a4a2e;
            font-weight: bold;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #777;
        }
        
        .back-links {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel - CRUD Menu</h1>
        <div class="admin-nav">
            <a href="admin_fix.php">Dashboard</a>
            <a href="admin_crud_menu.php" style="background: rgba(255,255,255,0.2);">Kelola Menu</a>
            <a href="admin_add_menu.php">Tambah Menu</a>
            <a href="menu_lengkap.php" target="_blank">Lihat Menu</a>
            <a href="index.php?logout=true" style="background: #e74c3c;">Logout</a>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2>Kelola Menu Produk</h2>
            <div>
                <a href="admin_add_menu.php" class="btn btn-success">➕ Tambah Produk Baru</a>
                <a href="menu_lengkap.php" target="_blank" class="btn">👁️ Lihat Menu Pelanggan</a>
            </div>
        </div>
        
        <?php if ($success_msg): ?>
            <div class="messages">
                <div class="success"><?php echo $success_msg; ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="messages">
                <div class="error"><?php echo $error_msg; ?></div>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
            <?php if (empty($menu_items)): ?>
                <div class="no-data">
                    <h3>📭 Belum ada produk</h3>
                    <p>Silakan tambahkan produk pertama Anda.</p>
                    <a href="admin_add_menu.php" class="btn btn-success" style="margin-top: 15px;">Tambah Produk Pertama</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menu_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $item['image_url']; ?>" 
                                         alt="<?php echo $item['name']; ?>" 
                                         class="product-image"
                                         onerror="this.src='https://via.placeholder.com/80?text=No+Image'">
                                </td>
                                <td>
                                    <strong><?php echo $item['name']; ?></strong><br>
                                    <small style="color: #666;"><?php echo substr($item['description'], 0, 50) . '...'; ?></small>
                                </td>
                                <td>
                                    <span class="category-badge">
                                        <?php 
                                        $categories = [
                                            'coffee' => '☕ Kopi',
                                            'non-coffee' => '🥤 Non-Kopi',
                                            'snack' => '🍪 Snack',
                                            'dessert' => '🍰 Dessert',
                                            'other' => '📦 Lainnya'
                                        ];
                                        echo $categories[$item['category']] ?? $item['category'];
                                        ?>
                                    </span>
                                </td>
                                <td class="price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($item['is_active']): ?>
                                        <span class="status-active">Aktif</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($item['created_at'])); ?><br>
                                    <small style="color: #888;"><?php echo date('H:i', strtotime($item['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="admin_edit_menu.php?id=<?php echo $item['id']; ?>" 
                                           class="btn btn-warning btn-sm">✏️ Edit</a>
                                        <a href="admin_crud_menu.php?toggle=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm" 
                                           style="background: <?php echo $item['is_active'] ? '#95a5a6' : '#27ae60'; ?>;">
                                            <?php echo $item['is_active'] ? '❌ Nonaktifkan' : '✅ Aktifkan'; ?>
                                        </a>
                                        <a href="admin_crud_menu.php?delete=<?php echo $item['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus <?php echo addslashes($item['name']); ?>?')">
                                            🗑️ Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="back-links">
            <a href="admin_fix.php" class="btn">← Kembali ke Dashboard</a>
            <a href="index.php" class="btn">🏠 Ke Halaman Utama</a>
        </div>
    </div>
    
    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.success, .error');
            messages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>