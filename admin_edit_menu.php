<?php
session_start();
include "config_db.php";

// CEK APAKAH ADMIN
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
$success_msg = '';
$error_msg = '';

// AMBIL DATA PRODUK
$product = null;
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if (!$product) {
    header("Location: admin_crud_menu.php");
    exit();
}

// PROSES UPDATE MENU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url'] ?? '');
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? 'coffee');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // VALIDASI
    if (empty($name) || $price <= 0) {
        $error_msg = "Nama produk dan harga wajib diisi!";
    } else {
        // UPDATE KE DATABASE
        $stmt = mysqli_prepare($conn, 
            "UPDATE menu_items SET 
                name = ?, 
                price = ?, 
                description = ?, 
                image_url = ?, 
                category = ?, 
                is_active = ?,
                updated_at = NOW()
             WHERE id = ?");
        
        mysqli_stmt_bind_param($stmt, 'sdsssii', $name, $price, $description, $image_url, $category, $is_active, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Produk <strong>$name</strong> berhasil diperbarui!";
            // Update product data
            $product['name'] = $name;
            $product['price'] = $price;
            $product['description'] = $description;
            $product['image_url'] = $image_url;
            $product['category'] = $category;
            $product['is_active'] = $is_active;
        } else {
            $error_msg = "Gagal memperbarui produk: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Admin Panel</title>
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
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: #7a4a2e;
            margin-bottom: 10px;
        }
        
        .product-info {
            background: #eef6fb;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            font-size: 14px;
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
        
        .btn-warning {
            background: #f39c12;
        }
        
        .btn-warning:hover {
            background: #d68910;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
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
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #7a4a2e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(122, 74, 46, 0.1);
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .image-preview {
            margin-top: 10px;
            text-align: center;
        }
        
        .image-preview img {
            max-width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #eee;
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .back-links {
            margin-top: 30px;
            text-align: center;
        }
        
        .product-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .status-active {
            background: #27ae60;
            color: white;
        }
        
        .status-inactive {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel - Edit Menu</h1>
        <div class="admin-nav">
            <a href="admin_fix.php">Dashboard</a>
            <a href="admin_crud_menu.php">Kelola Menu</a>
            <a href="admin_add_menu.php">Tambah Menu</a>
            <a href="menu_lengkap.php" target="_blank">Lihat Menu</a>
            <a href="index.php?logout=true" style="background: #e74c3c;">Logout</a>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2>✏️ Edit Produk</h2>
            <p>Edit data produk: <strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
            <div class="product-info">
                <strong>Info Produk:</strong><br>
                ID: #<?php echo $product['id']; ?> | 
                Dibuat: <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?> | 
                Status: <span class="product-status <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo $product['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                </span>
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
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="required">Nama Produk</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" 
                               placeholder="Contoh: Latte Coconut Breeze" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price" class="required">Harga (Rp)</label>
                        <input type="number" id="price" name="price" 
                               value="<?php echo htmlspecialchars($product['price']); ?>" 
                               placeholder="25000" min="1000" step="500" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category">
                        <option value="coffee" <?php echo $product['category'] == 'coffee' ? 'selected' : ''; ?>>☕ Kopi</option>
                        <option value="non-coffee" <?php echo $product['category'] == 'non-coffee' ? 'selected' : ''; ?>>🥤 Non-Kopi</option>
                        <option value="snack" <?php echo $product['category'] == 'snack' ? 'selected' : ''; ?>>🍪 Snack</option>
                        <option value="dessert" <?php echo $product['category'] == 'dessert' ? 'selected' : ''; ?>>🍰 Dessert</option>
                        <option value="other" <?php echo $product['category'] == 'other' ? 'selected' : ''; ?>>📦 Lainnya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi Produk</label>
                    <textarea id="description" name="description" 
                              placeholder="Deskripsi singkat tentang produk..."><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image_url">URL Gambar Produk</label>
                    <input type="url" id="image_url" name="image_url" 
                           value="<?php echo htmlspecialchars($product['image_url']); ?>" 
                           placeholder="https://images.unsplash.com/photo-..."
                           oninput="updateImagePreview()">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Biarkan kosong untuk menggunakan gambar default
                    </small>
                    <div class="image-preview" id="imagePreview">
                        <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/200x150?text=Pratinjau+Gambar'; ?>" 
                             alt="Preview" id="previewImage"
                             onerror="this.src='https://via.placeholder.com/200x150?text=Gambar+Tidak+Valid'">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                        <label for="is_active">Produk aktif (tampil di menu)</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
                    <a href="admin_crud_menu.php" class="btn btn-secondary">❌ Batal</a>
                    <a href="admin_crud_menu.php?delete=<?php echo $product['id']; ?>" 
                       class="btn" 
                       style="background: #e74c3c;"
                       onclick="return confirm('Yakin ingin menghapus <?php echo addslashes($product['name']); ?>?')">
                        🗑️ Hapus Produk
                    </a>
                </div>
            </form>
        </div>
        
        <div class="back-links">
            <a href="admin_crud_menu.php" class="btn">← Kembali ke Kelola Menu</a>
            <a href="admin_fix.php" class="btn">📊 Ke Dashboard</a>
        </div>
    </div>
    
    <script>
        // Update image preview
        function updateImagePreview() {
            const url = document.getElementById('image_url').value;
            const preview = document.getElementById('previewImage');
            
            if (url) {
                preview.src = url;
                preview.onerror = function() {
                    this.src = 'https://via.placeholder.com/200x150?text=URL+Gambar+Tidak+Valid';
                };
            } else {
                preview.src = 'https://via.placeholder.com/200x150?text=Pratinjau+Gambar';
            }
        }
        
        // Auto-hide messages
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