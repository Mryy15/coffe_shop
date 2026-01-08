<?php
// menu.php
session_start();
include "config_db.php";

// Query semua menu
$sql = "SELECT * FROM menu_items ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Lengkap - Coffee Shop</title>
    <style>
        /* Copy style dari index.php */
        :root {
            --coffee: #7a4a2e;
            --soft-sky: #eef6fb;
            --soft-sand: #f7efe5;
        }
        
        * { box-sizing: border-box; scroll-behavior: smooth; }
        
        body {
            margin: 0;
            font-family: "Inter", sans-serif;
            background: linear-gradient(180deg, var(--soft-sky), var(--soft-sand));
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 100px 20px 50px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        h1 {
            font-family: "Dancing Script", cursive;
            font-size: 3.5rem;
            color: var(--coffee);
            margin-bottom: 10px;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 30px;
            padding: 10px 20px;
            background: var(--coffee);
            color: white;
            text-decoration: none;
            border-radius: 20px;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .menu-item {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 12px 30px rgba(0,0,0,.12);
            transition: .3s;
        }
        
        .menu-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,.15);
        }
        
        .menu-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
        }
        
        .menu-item h3 {
            margin: 10px 0;
            color: var(--coffee);
        }
        
        .price {
            color: var(--coffee);
            font-weight: 700;
            font-size: 1.2rem;
            margin: 10px 0;
        }
        
        .category {
            display: inline-block;
            background: #f0e6d6;
            color: var(--coffee);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .add-cart {
            background: var(--coffee);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            transition: .3s;
        }
        
        .add-cart:hover {
            background: #5a3822;
        }
        
        .empty-menu {
            text-align: center;
            padding: 50px;
            font-size: 1.2rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 80px 15px 30px;
            }
            
            h1 {
                font-size: 2.5rem;
            }
            
            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">&larr; Kembali ke Home</a>
        
        <div class="header">
            <h1>Menu Lengkap</h1>
            <p>Nikmati berbagai pilihan kopi dan makanan kami</p>
        </div>
        
        <?php if($result && mysqli_num_rows($result) > 0): ?>
            <div class="menu-grid">
                <?php while($item = mysqli_fetch_assoc($result)): ?>
                <div class="menu-item" 
                     data-name="<?php echo htmlspecialchars($item['name']); ?>"
                     data-price="<?php echo $item['price']; ?>">
                    <?php if($item['category']): ?>
                        <span class="category"><?php echo ucfirst($item['category']); ?></span>
                    <?php endif; ?>
                    
                    <img src="<?php echo $item['image_url']; ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
                    
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    
                    <p class="price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                    
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button class="add-cart" onclick="addToCart(this)">Tambah ke Keranjang</button>
                    <?php else: ?>
                        <button class="add-cart" onclick="alert('Silakan login terlebih dahulu'); window.location.href='index.php?login=success';">Tambah ke Keranjang</button>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-menu">
                <p>Belum ada menu yang tersedia.</p>
                <p>Admin dapat menambahkan menu melalui Admin Panel.</p>
                <a href="index.php" class="back-btn">Kembali ke Home</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Fungsi tambah ke keranjang
        function addToCart(button) {
            const item = button.closest('.menu-item');
            const name = item.dataset.name;
            const price = parseInt(item.dataset.price);
            
            // Ambil cart dari localStorage
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            // Cek apakah item sudah ada
            const existingItem = cart.find(item => item.name === name);
            
            if (existingItem) {
                existingItem.qty += 1;
            } else {
                cart.push({
                    name: name,
                    price: price,
                    qty: 1
                });
            }
            
            // Simpan ke localStorage
            localStorage.setItem('cart', JSON.stringify(cart));
            
            alert(name + ' berhasil ditambahkan ke keranjang!');
            
            // Update cart count
            updateCartCount();
        }
        
        // Update cart count
        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let total = 0;
            cart.forEach(item => total += item.qty);
            
            // Update di halaman ini jika ada cart count
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.innerText = total;
            }
        }
        
        // Panggil updateCartCount saat halaman dimuat
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>
</html>
<?php
// Tutup koneksi
if (isset($conn)) {
    mysqli_close($conn);
}
?>