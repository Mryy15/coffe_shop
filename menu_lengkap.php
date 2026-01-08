<?php
session_start();
include "config_db.php";

// Ambil semua produk AKTIF dari database
$sql = "SELECT * FROM menu_items WHERE is_active = 1 ORDER BY 
        CASE category 
            WHEN 'coffee' THEN 1
            WHEN 'non-coffee' THEN 2
            WHEN 'snack' THEN 3
            WHEN 'dessert' THEN 4
            ELSE 5
        END, name";
        
$result = mysqli_query($conn, $sql);
$menu_items = [];
$categories = [];

if ($result && mysqli_num_rows($result) > 0) {
    while($item = mysqli_fetch_assoc($result)) {
        $menu_items[] = $item;
        $categories[$item['category']][] = $item;
    }
} else {
    // Fallback data jika database kosong
    $menu_items = [
        [
            'id' => 1,
            'name' => 'Caramel Macchiato',
            'price' => 32000,
            'description' => 'Kopi dengan rasa karamel yang manis',
            'image_url' => 'https://images.unsplash.com/photo-1561047029-3000c68339ca',
            'category' => 'coffee'
        ],
        [
            'id' => 2,
            'name' => 'Mocha Delight',
            'price' => 28000,
            'description' => 'Perpaduan coklat dan kopi yang sempurna',
            'image_url' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96',
            'category' => 'coffee'
        ],
        [
            'id' => 3,
            'name' => 'Americano Classic',
            'price' => 22000,
            'description' => 'Kopi hitam klasik yang kuat',
            'image_url' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93',
            'category' => 'coffee'
        ],
        [
            'id' => 4,
            'name' => 'Green Tea Latte',
            'price' => 27000,
            'description' => 'Teh hijau dengan susu lembut',
            'image_url' => 'https://images.unsplash.com/photo-1556740749-887f6717d7e4',
            'category' => 'non-coffee'
        ],
        [
            'id' => 5,
            'name' => 'Chocolate Chip Cookie',
            'price' => 15000,
            'description' => 'Cookie coklat chip yang lembut',
            'image_url' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e',
            'category' => 'snack'
        ],
        [
            'id' => 6,
            'name' => 'Cheesecake',
            'price' => 35000,
            'description' => 'Cheesecake lembut dengan topping buah',
            'image_url' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187',
            'category' => 'dessert'
        ]
    ];
    
    // Group by category for fallback
    foreach ($menu_items as $item) {
        $categories[$item['category']][] = $item;
    }
}

mysqli_close($conn);

// Nama kategori yang lebih user-friendly
$category_names = [
    'coffee' => '☕ Kopi Spesial',
    'non-coffee' => '🥤 Minuman Non-Kopi',
    'snack' => '🍪 Snack & Camilan',
    'dessert' => '🍰 Dessert & Kue',
    'other' => '📦 Lainnya'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Lengkap - Coffee Shop</title>
    
    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    
    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --coffee: #7a4a2e;
            --light-coffee: #9b6b4a;
            --cream: #f7efe5;
            --light-cream: #fcf8f3;
            --text: #333;
            --text-light: #666;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-cream);
            color: var(--text);
            line-height: 1.6;
        }
        
        /* HEADER */
        .header {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-family: 'Dancing Script', cursive;
            font-size: 2.2rem;
            color: var(--coffee);
            text-decoration: none;
            font-weight: 700;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            padding: 8px 0;
            position: relative;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: var(--coffee);
        }
        
        .nav-links a.active {
            color: var(--coffee);
            font-weight: 600;
        }
        
        .nav-links a.active::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 3px;
            background: var(--coffee);
            bottom: -5px;
            left: 0;
            border-radius: 2px;
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .auth-btn {
            padding: 8px 20px;
            background: var(--coffee);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .auth-btn:hover {
            background: var(--light-coffee);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(122, 74, 46, 0.2);
        }
        
        .cart-icon {
            position: relative;
            font-size: 1.3rem;
            color: var(--coffee);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .cart-icon:hover {
            background: rgba(122, 74, 46, 0.1);
        }
        
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 50%;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
        }
        
        /* HERO SECTION */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                        url('https://images.unsplash.com/photo-1498804103079-a6351b050096?auto=format&fit=crop&w=1400&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 20px;
            margin-bottom: 50px;
        }
        
        .hero h1 {
            font-family: 'Dancing Script', cursive;
            font-size: 4.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
            line-height: 1.8;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--cream);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* CATEGORY NAV */
        .category-nav {
            max-width: 1200px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }
        
        .category-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .cat-btn {
            padding: 12px 25px;
            border: 2px solid var(--coffee);
            background: transparent;
            color: var(--coffee);
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .cat-btn.active,
        .cat-btn:hover {
            background: var(--coffee);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(122, 74, 46, 0.2);
        }
        
        /* MENU SECTIONS */
        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }
        
        .category-section {
            margin-bottom: 60px;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .category-title {
            font-family: 'Dancing Script', cursive;
            font-size: 3rem;
            color: var(--coffee);
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .category-title::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 3px;
            background: var(--coffee);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .menu-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        
        .menu-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .menu-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .menu-card:hover .menu-image {
            transform: scale(1.05);
        }
        
        .menu-content {
            padding: 25px;
            position: relative;
        }
        
        .menu-content h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--text);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .menu-price {
            color: var(--coffee);
            font-size: 1.4rem;
            font-weight: 700;
            background: rgba(122, 74, 46, 0.1);
            padding: 5px 15px;
            border-radius: 20px;
        }
        
        .menu-description {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 20px;
            line-height: 1.7;
        }
        
        .add-cart-btn {
            width: 100%;
            padding: 14px;
            background: var(--coffee);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .add-cart-btn:hover {
            background: var(--light-coffee);
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(122, 74, 46, 0.25);
        }
        
        .add-cart-btn.added {
            background: #27ae60;
        }
        
        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }
        
        /* FOOTER */
        .footer {
            background: var(--coffee);
            color: white;
            padding: 60px 20px 30px;
            margin-top: 80px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .footer-section h3 {
            font-family: 'Dancing Script', cursive;
            font-size: 2.2rem;
            margin-bottom: 20px;
        }
        
        .footer-section p {
            opacity: 0.9;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            color: white;
            font-size: 1.3rem;
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .social-links a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-3px);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .copyright {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }
        
        /* BACK TO TOP */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--coffee);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1.3rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 100;
            transition: all 0.3s;
            opacity: 0;
            visibility: hidden;
        }
        
        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            background: var(--light-coffee);
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        
        /* RESPONSIVE */
        @media (max-width: 992px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }
            
            .hero h1 {
                font-size: 3.5rem;
            }
            
            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .hero {
                padding: 70px 20px;
            }
            
            .hero h1 {
                font-size: 2.8rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .category-title {
                font-size: 2.5rem;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .social-links {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .category-buttons {
                gap: 10px;
            }
            
            .cat-btn {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .menu-content {
                padding: 20px;
            }
            
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">Coffee Shop</a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="menu_lengkap.php" class="active">Menu Lengkap</a></li>
                <li><a href="index.php#gallery">Galeri</a></li>
                <li><a href="index.php#about">Tentang Kami</a></li>
                <li><a href="index.php#contact">Kontak</a></li>
            </ul>
            
            <div class="nav-right">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span style="font-weight: 600; color: var(--coffee); margin-right: 10px;">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <?php if(($_SESSION['role'] ?? '') == 'admin'): ?>
                        <a href="admin_crud_menu.php" class="auth-btn" style="background: #e74c3c;">
                            <i class="fas fa-cog"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="index.php?logout=true" class="auth-btn">Logout</a>
                <?php else: ?>
                    <a href="index.php" class="auth-btn">Login / Daftar</a>
                <?php endif; ?>
                
                <div class="cart-icon" onclick="window.location.href='index.php#cart'">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- HERO SECTION -->
    <section class="hero">
        <h1>Menu Lengkap Kami</h1>
        <p>Temukan berbagai pilihan kopi spesial, minuman menyegarkan, dan camilan lezat yang dibuat dengan bahan terbaik dan penuh cinta.</p>
        
        <div class="hero-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($menu_items); ?>+</div>
                <div class="stat-label">Pilihan Menu</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">4</div>
                <div class="stat-label">Kategori</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">Fresh Ingredients</div>
            </div>
        </div>
    </section>
    
    <!-- CATEGORY NAVIGATION -->
    <div class="category-nav">
        <div class="category-buttons">
            <button class="cat-btn active" data-category="all">
                <i class="fas fa-star"></i> Semua Menu
            </button>
            <?php foreach ($categories as $cat => $items): ?>
                <button class="cat-btn" data-category="<?php echo $cat; ?>">
                    <?php 
                    $icons = [
                        'coffee' => 'fas fa-coffee',
                        'non-coffee' => 'fas fa-glass-whiskey',
                        'snack' => 'fas fa-cookie-bite',
                        'dessert' => 'fas fa-birthday-cake',
                        'other' => 'fas fa-box'
                    ];
                    ?>
                    <i class="<?php echo $icons[$cat] ?? 'fas fa-utensils'; ?>"></i>
                    <?php echo $category_names[$cat] ?? ucfirst($cat); ?>
                    <span class="item-count">(<?php echo count($items); ?>)</span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- MENU SECTIONS -->
    <div class="menu-container">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-coffee"></i>
                <h3>Menu Belum Tersedia</h3>
                <p>Maaf, saat ini menu sedang dalam persiapan.</p>
                <a href="index.php" class="auth-btn">Kembali ke Home</a>
            </div>
        <?php else: ?>
            <!-- ALL MENU VIEW -->
            <div class="category-section" id="all-menu">
                <h2 class="category-title">Semua Pilihan Menu</h2>
                <div class="menu-grid">
                    <?php foreach ($menu_items as $item): ?>
                        <div class="menu-card" data-category="<?php echo $item['category']; ?>">
                            <img src="<?php echo $item['image_url']; ?>" 
                                 alt="<?php echo $item['name']; ?>" 
                                 class="menu-image"
                                 onerror="this.src='https://via.placeholder.com/400x220?text=No+Image'">
                            
                            <div class="menu-content">
                                <h3>
                                    <?php echo $item['name']; ?>
                                    <span class="menu-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                </h3>
                                
                                <p class="menu-description">
                                    <?php echo $item['description'] ?? 'Minuman spesial dengan rasa yang unik.'; ?>
                                </p>
                                
                                <button class="add-cart-btn" 
                                        data-id="<?php echo $item['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($item['name']); ?>" 
                                        data-price="<?php echo $item['price']; ?>"
                                        data-image="<?php echo $item['image_url']; ?>">
                                    <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- CATEGORY VIEWS (Hidden by default) -->
            <?php foreach ($categories as $cat => $items): ?>
                <div class="category-section" id="category-<?php echo $cat; ?>" style="display: none;">
                    <h2 class="category-title">
                        <?php echo $category_names[$cat] ?? ucfirst($cat); ?>
                    </h2>
                    
                    <div class="menu-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="menu-card">
                                <img src="<?php echo $item['image_url']; ?>" 
                                     alt="<?php echo $item['name']; ?>" 
                                     class="menu-image"
                                     onerror="this.src='https://via.placeholder.com/400x220?text=No+Image'">
                                
                                <div class="menu-content">
                                    <h3>
                                        <?php echo $item['name']; ?>
                                        <span class="menu-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                    </h3>
                                    
                                    <p class="menu-description">
                                        <?php echo $item['description'] ?? 'Minuman spesial dengan rasa yang unik.'; ?>
                                    </p>
                                    
                                    <button class="add-cart-btn" 
                                            data-id="<?php echo $item['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>" 
                                            data-price="<?php echo $item['price']; ?>"
                                            data-image="<?php echo $item['image_url']; ?>">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Coffee Shop</h3>
                <p>Nikmati kopi terbaik dengan suasana pantai yang menenangkan. Setiap cangkir dibuat dengan cinta dan bahan terbaik.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Jam Operasional</h4>
                <ul class="footer-links">
                    <li>Senin - Jumat: 07:00 - 22:00</li>
                    <li>Sabtu - Minggu: 08:00 - 23:00</li>
                    <li>Hari Libur: 09:00 - 21:00</li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu_lengkap.php">Menu Lengkap</a></li>
                    <li><a href="index.php#gallery">Galeri</a></li>
                    <li><a href="index.php#about">Tentang Kami</a></li>
                    <li><a href="index.php#contact">Kontak & Reservasi</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Kontak</h4>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt"></i> Jl. Pantai Indah No. 123</li>
                    <li><i class="fas fa-phone"></i> (021) 1234-5678</li>
                    <li><i class="fas fa-envelope"></i> info@coffeeshop.com</li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            &copy; 2025 Coffee Shop. All rights reserved. | Made with <i class="fas fa-heart" style="color: #ff6b6b;"></i>
        </div>
    </footer>
    
    <!-- BACK TO TOP -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <!-- JAVASCRIPT -->
    <script>
        // Cart System
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        // Update cart count
        function updateCartCount() {
            let total = 0;
            cart.forEach(item => total += item.quantity || 1);
            document.getElementById('cartCount').textContent = total;
        }
        
        // Initialize cart count
        updateCartCount();
        
        // Add to cart functionality
        document.querySelectorAll('.add-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                const productPrice = parseInt(this.dataset.price);
                const productImage = this.dataset.image;
                
                // Check if user is logged in
                <?php if(!isset($_SESSION['user_id'])): ?>
                    if (confirm('Silakan login terlebih dahulu untuk menambahkan ke keranjang.\n\nLogin sekarang?')) {
                        window.location.href = 'index.php';
                    }
                    return;
                <?php endif; ?>
                
                // Find existing item in cart
                const existingItem = cart.find(item => item.id === productId);
                
                if (existingItem) {
                    existingItem.quantity = (existingItem.quantity || 1) + 1;
                } else {
                    cart.push({
                        id: productId,
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        quantity: 1
                    });
                }
                
                // Save to localStorage
                localStorage.setItem('cart', JSON.stringify(cart));
                
                // Update cart count
                updateCartCount();
                
                // Visual feedback
                const originalText = this.innerHTML;
                const originalBg = this.style.background;
                
                this.innerHTML = '<i class="fas fa-check"></i> Ditambahkan!';
                this.classList.add('added');
                this.style.background = '#27ae60';
                this.disabled = true;
                
                // Show notification
                showNotification(`✅ ${productName} berhasil ditambahkan ke keranjang!`);
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('added');
                    this.style.background = originalBg;
                    this.disabled = false;
                }, 2000);
            });
        });
        
        // Category filtering
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.dataset.category;
                
                // Update active button
                document.querySelectorAll('.cat-btn').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');
                
                // Show/hide sections
                if (category === 'all') {
                    document.getElementById('all-menu').style.display = 'block';
                    <?php foreach ($categories as $cat => $items): ?>
                        document.getElementById('category-<?php echo $cat; ?>').style.display = 'none';
                    <?php endforeach; ?>
                } else {
                    document.getElementById('all-menu').style.display = 'none';
                    <?php foreach ($categories as $cat => $items): ?>
                        document.getElementById('category-<?php echo $cat; ?>').style.display = 
                            '<?php echo $cat; ?>' === category ? 'block' : 'none';
                    <?php endforeach; ?>
                }
                
                // Smooth scroll to menu
                document.querySelector('.menu-container').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
        
        // Back to top button
        const backToTop = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Notification function
        function showNotification(message) {
            // Remove existing notification
            const existing = document.querySelector('.notification');
            if (existing) existing.remove();
            
            // Create new notification
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #27ae60;
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideIn 0.3s ease-out;
                max-width: 350px;
                font-weight: 500;
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0);