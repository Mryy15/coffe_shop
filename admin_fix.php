<?php
session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include "config_db.php";

// PROSES TAMBAH MENU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_menu'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    
    // Upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/menu/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        // Validasi file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $target_file;
            }
        }
    }
    
    // Jika tidak ada gambar upload, gunakan gambar default
    if (empty($gambar)) {
        $gambar = 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=400&q=80';
    }
    
    $sql = "INSERT INTO menu_items (name, description, price, image_url, category) 
            VALUES ('$nama', '$deskripsi', '$harga', '$gambar', '$kategori')";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Menu berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan menu: " . mysqli_error($conn);
    }
}

// PROSES EDIT MENU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_menu'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    
    $sql = "UPDATE menu_items SET 
            name = '$nama', 
            description = '$deskripsi', 
            price = '$harga', 
            category = '$kategori' 
            WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Menu berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui menu: " . mysqli_error($conn);
    }
}

// PROSES HAPUS MENU
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $sql = "DELETE FROM menu_items WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Menu berhasil dihapus!";
    } else {
        $error = "Gagal menghapus menu: " . mysqli_error($conn);
    }
}

// Ambil semua menu untuk ditampilkan
$sql_menu = "SELECT * FROM menu_items ORDER BY id DESC";
$result_menu = mysqli_query($conn, $sql_menu);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Coffee Shop</title>
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
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
            transform: translateY(-2px);
        }
        
        .admin-btn.logout {
            background: #e74c3c;
        }
        
        .admin-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .form-section, .list-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: var(--coffee);
            margin-bottom: 20px;
            font-family: 'Dancing Script', cursive;
            font-size: 2rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn-submit {
            background: var(--coffee);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
        }
        
        .btn-submit:hover {
            background: #5a3822;
            transform: translateY(-2px);
        }
        
        .menu-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .menu-table th, .menu-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .menu-table th {
            background: #f8f8f8;
            color: var(--coffee);
            font-weight: 600;
        }
        
        .menu-table tr:hover {
            background: #f9f9f9;
        }
        
        .menu-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit, .btn-hapus {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-hapus {
            background: #e74c3c;
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .admin-content {
                grid-template-columns: 1fr;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Admin Panel - Coffee Shop</h1>
            <div class="admin-nav">
                <a href="index.php" class="admin-btn">Kembali ke Home</a>
                <a href="index.php?logout=true" class="admin-btn logout">Logout</a>
            </div>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-content">
            <!-- FORM TAMBAH/EDIT MENU -->
            <div class="form-section">
                <h2><?php echo isset($_GET['edit']) ? 'Edit Menu' : 'Tambah Menu Baru'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if(isset($_GET['edit'])): 
                        $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
                        $sql_edit = "SELECT * FROM menu_items WHERE id = '$edit_id'";
                        $result_edit = mysqli_query($conn, $sql_edit);
                        $menu_edit = mysqli_fetch_assoc($result_edit);
                    ?>
                        <input type="hidden" name="id" value="<?php echo $menu_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="nama">Nama Menu</label>
                        <input type="text" id="nama" name="nama" 
                               value="<?php echo isset($menu_edit['name']) ? $menu_edit['name'] : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" required><?php echo isset($menu_edit['description']) ? $menu_edit['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" 
                               value="<?php echo isset($menu_edit['price']) ? $menu_edit['price'] : ''; ?>" 
                               required min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="kopi" <?php echo (isset($menu_edit['category']) && $menu_edit['category'] == 'kopi') ? 'selected' : ''; ?>>Kopi</option>
                            <option value="non-kopi" <?php echo (isset($menu_edit['category']) && $menu_edit['category'] == 'non-kopi') ? 'selected' : ''; ?>>Non-Kopi</option>
                            <option value="snack" <?php echo (isset($menu_edit['category']) && $menu_edit['category'] == 'snack') ? 'selected' : ''; ?>>Snack</option>
                            <option value="makanan" <?php echo (isset($menu_edit['category']) && $menu_edit['category'] == 'makanan') ? 'selected' : ''; ?>>Makanan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="gambar">Gambar Menu</label>
                        <input type="file" id="gambar" name="gambar" accept="image/*">
                        <?php if(isset($menu_edit['image_url'])): ?>
                            <p style="margin-top: 5px; font-size: 14px; color: #666;">
                                Gambar saat ini: <a href="<?php echo $menu_edit['image_url']; ?>" target="_blank">Lihat</a>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-submit" name="<?php echo isset($_GET['edit']) ? 'edit_menu' : 'tambah_menu'; ?>">
                        <?php echo isset($_GET['edit']) ? 'Update Menu' : 'Tambah Menu'; ?>
                    </button>
                    
                    <?php if(isset($_GET['edit'])): ?>
                        <a href="admin_fix.php" class="admin-btn" style="display: inline-block; margin-left: 10px;">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- DAFTAR MENU -->
            <div class="list-section">
                <h2>Daftar Menu</h2>
                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result_menu && mysqli_num_rows($result_menu) > 0): 
                            $no = 1;
                            while($menu = mysqli_fetch_assoc($result_menu)):
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <img src="<?php echo $menu['image_url']; ?>" 
                                         alt="<?php echo $menu['name']; ?>" 
                                         class="menu-img"
                                         onerror="this.src='https://via.placeholder.com/80x60?text=No+Image'">
                                </td>
                                <td>
                                    <strong><?php echo $menu['name']; ?></strong><br>
                                    <small style="color: #666;"><?php echo substr($menu['description'], 0, 50) . '...'; ?></small>
                                </td>
                                <td>Rp <?php echo number_format($menu['price'], 0, ',', '.'); ?></td>
                                <td><?php echo ucfirst($menu['category'] ?? '-'); ?></td>
                                <td class="action-buttons">
                                    <a href="admin_fix.php?edit=<?php echo $menu['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="admin_fix.php?hapus=<?php echo $menu['id']; ?>" 
                                       class="btn-hapus" 
                                       onclick="return confirm('Yakin hapus menu ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">
                                    Belum ada menu. Silakan tambah menu baru.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Konfirmasi sebelum hapus
        function confirmDelete() {
            return confirm("Apakah Anda yakin ingin menghapus menu ini?");
        }
        
        // Format harga input
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga');
            if (hargaInput) {
                hargaInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    e.target.value = value;
                });
            }
        });
    </script>
</body>
</html>

<?php
// Tutup koneksi database
if (isset($conn)) {
    mysqli_close($conn);
}
?>