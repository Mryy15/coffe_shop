<?php
session_start();

/* ===============================
   KONEKSI DATABASE (WAJIB DI ATAS)
   =============================== */
include "config_db.php"; // <-- INI YANG SEBELUMNYA KURANG

/* ===============================
   LOGOUT
   =============================== */
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: index.php");
    exit();
}

/* ===============================
   PROSES LOGIN
   =============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {

    // VALIDASI KONEKSI
    if (!$conn) {
        die("Koneksi database gagal.");
    }

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users 
            WHERE username = '$username' 
               OR email = '$username'
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        // ⚠️ DEMO LOGIN (TANPA PASSWORD VERIFY)
        // PRODUKSI: gunakan password_verify()
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['role'] = $user['role'];

        header("Location: index.php");
        exit();

    } else {
        // DEMO AUTO LOGIN
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $username . '@coffee.com';
        $_SESSION['role'] = (strtolower($username) === 'admin') ? 'admin' : 'user';

        header("Location: index.php");
        exit();
    }
}

/* ===============================
   AUTO LOGIN VIA URL
   =============================== */
if (isset($_GET['login']) && $_GET['login'] === 'success') {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Pengguna';
    $_SESSION['role'] = 'user';
    header("Location: index.php");
    exit();
}

if (isset($_GET['login']) && $_GET['login'] === 'admin') {
    $_SESSION['user_id'] = 999;
    $_SESSION['username'] = 'admin';
    $_SESSION['email'] = 'admin@coffee.com';
    $_SESSION['role'] = 'admin';
    header("Location: index.php");
    exit();
}

/* ===============================
   CLEAN ERROR SESSION
   =============================== */
if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}

/* ===============================
   DEBUG (AMAN DALAM HTML COMMENT)
   =============================== */
echo "<!-- DEBUG SESSION: ";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . " | ";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . " | ";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . " | ";
echo "Email: " . ($_SESSION['email'] ?? 'NOT SET');
echo " -->";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Coffee Shop</title>

  <!-- GOOGLE FONT -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">

  <!-- AOS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

  <!-- FONT AWESOME CDN (ikon sosial) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --coffee: #7a4a2e;
      --soft-sky: #eef6fb;
      --soft-sand: #f7efe5;
      --glass: rgba(255,255,255,0.75);
    }

    * { box-sizing: border-box; scroll-behavior: smooth; }

    body {
      margin: 0;
      font-family: "Inter", sans-serif;
      background: linear-gradient(180deg, var(--soft-sky), var(--soft-sand));
      color: #333;
    }

    /* ===== NAVBAR ===== */
    header {
      position: fixed;
      top: 0;
      width: 100%;
      background: var(--glass);
      backdrop-filter: blur(14px);
      box-shadow: 0 8px 25px rgba(255, 0, 0, 0.08);
      z-index: 9999;
    }

    nav {
      max-width: 1200px;
      margin: auto;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 50px;
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: 50px;
    }

    nav h1 {
      font-family: "Dancing Script", cursive;
      font-size: 2rem;
      color: var(--coffee);
      margin: 0;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 25px;
      margin: 0;
      padding: 0;
    }

    nav ul li a {
      text-decoration: none;
      color: #444;
      font-weight: 500;
      position: relative;
    }

    nav ul li a::after {
      content: "";
      position: absolute;
      width: 0;
      height: 2px;
      left: 0;
      bottom: -6px;
      background: var(--coffee);
      transition: 0.3s;
    }

    nav ul li a:hover::after { width: 100%; }

    /* ===== HERO ===== */
    .hero {
      position: relative;
      height: 100vh;
      background: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)),
        url("https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1400&q=80") center/cover fixed;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding-top: 80px;
      overflow: hidden;
    }

    .hero-content { color: white; max-width: 720px; }

    .hero h2 {
      font-family: "Dancing Script", cursive;
      font-size: 5rem;
      color: #fff;
      margin-bottom: 10px;
    }

    .hero-subtitle {
      font-style: italic;
      font-size: 1.3rem;
      opacity: 0.9;
      margin-bottom: 20px;
    }

    .hero p { font-size: 1.2rem; line-height: 1.8; opacity: .95; margin-bottom: 35px; position: relative;  z-index: 1;}

    .btn {
      padding: 15px 38px;
      background: linear-gradient(45deg, #7a4a2e, #9b6b4a);
      color: white;
      text-decoration: none;
      border-radius: 30px;
      font-weight: 600;
      box-shadow: 0 10px 25px rgba(0,0,0,.25);
      transition: .3s;
    }

    .btn:hover { transform: translateY(-4px); }

    section { max-width: 1200px; margin: auto; padding: 100px 20px; }

    h2 {
      text-align: center;
      color: var(--coffee);
      font-size: 2.6rem;
      margin-bottom: 50px;
      font-family: "Dancing Script", cursive;
    }

    /* ===== WAVE ===== */
    .wave {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 100px;
      background: url('https://www.svgrepo.com/download/472965/wave.svg') repeat-x;
      background-size: cover;
      animation: waveAnim 18s linear infinite;
      opacity: 0.5;
    }

    @keyframes waveAnim {
      0% { background-position-x: 0; }
      100% { background-position-x: 1000px; }
    }

    /* ===== MENU ===== */
    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
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

    .menu-item:hover { transform: translateY(-8px); }

    .menu-item img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 15px;
      margin-bottom: 18px;
    }

    .price { color: var(--coffee); font-weight: 700; }

    /* ===== GALLERY ===== */
    #gallery { position: relative; overflow: hidden; }

    #gallery::before {
      content: "☀️";
      font-size: 4rem;
      position: absolute;
      top: 10px;
      left: 20px;
      opacity: 0.15;
    }

    #gallery::after {
      content: "☕";
      font-size: 2.5rem;
      position: absolute;
      bottom: 10px;
      right: 15px;
      opacity: 0.15;
    }

    .gallery {
      display: flex;
      gap: 20px;
      overflow-x: auto;
      padding-bottom: 10px;
    }

    .gallery img {
      width: 350px;
      height: 230px;
      object-fit: cover;
      border-radius: 20px;
      transition: .3s;
      box-shadow: 0 10px 20px rgba(0,0,0,.1);
    }

    .gallery img:hover { transform: scale(1.05); }

    /* ===== ABOUT ===== */
    #about {
      position: relative;
      background: rgba(255,255,255,0.65);
      border-radius: 25px;
      padding: 80px 20px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      overflow: hidden;
      transition: 0.3s;
    }

    #about:hover {
      transform: scale(1.02);
      box-shadow: 0 14px 30px rgba(0,0,0,0.12);
    }

    #about::before {
      content: "☕";
      font-size: 4rem;
      position: absolute;
      top: 20px;
      left: 20px;
      opacity: 0.15;
    }

    #about::after {
      content: "🌊";
      font-size: 4rem;
      position: absolute;
      bottom: 20px;
      right: 30px;
      opacity: 0.15;
    }

    .about-text {
      max-width: 800px;
      margin: auto;
      text-align: center;
      line-height: 1.9;
      font-size: 1.05rem;
    }

    .testimonial {
      margin-top: 35px;
      background: rgba(255,255,255,.85);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,.1);
      text-align: center;
      font-style: italic;
    }

    #about a {
      text-decoration: none;
      color: inherit;
      display: block;
    }

    /* ===== CONTACT ===== */
    .reservation form {
      max-width: 500px;
      margin: auto;
      display: grid;
      gap: 15px;
    }

    .reservation input, .reservation button {
      padding: 13px;
      border-radius: 10px;
      border: none;
      font-family: "Inter", sans-serif;
    }

    .reservation input { background: #f5f5f5; }

    .reservation button {
      background: var(--coffee);
      color: white;
      font-weight: 600;
      cursor: pointer;
    }

    /* ===== Login ===== */
    .auth-area {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .auth-btn {
      padding: 7px 16px;
      border-radius: 20px;
      border: none;
      background: var(--coffee);
      color: white;
      font-weight: 600;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }

    .auth-btn.outline {
      background: transparent;
      color: var(--coffee);
      border: 2px solid var(--coffee);
    }

    .user-greeting {
      font-weight: 600;
      color: var(--coffee);
      margin-right: 10px;
    }

    /* ===== SOCIAL ICONS ===== */
    .social-icons {
      display: flex;
      justify-content: center;
      gap: 25px;
      margin-top: 40px;
    }

    .social-icons a {
      color: var(--coffee);
      font-size: 1.8rem;
      transition: 0.3s;
    }

    .social-icons a:hover {
      color: #000;
      transform: scale(1.1);
    }

    footer {
      background: var(--coffee);
      color: white;
      text-align: center;
      padding: 30px;
    }
    
    /* ===== LOGIN MODAL ===== */
    .login-modal {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.55);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
    }

    .login-box {
      background: white;
      padding: 30px;
      border-radius: 20px;
      width: 320px;
      text-align: center;
    }

    .login-box h2 {
      margin-bottom: 15px;
      color: var(--coffee);
    }

    .login-box input {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .login-box button {
      width: 100%;
      padding: 10px;
      background: var(--coffee);
      color: white;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 600;
    }

    .close-login {
      margin-top: 10px;
      cursor: pointer;
      color: #777;
    }

    /* ===== KERANJANG ===== */
    .cart-icon {
      position: relative;
      font-size: 22px;
      color: #f5c542;
      cursor: pointer;
      margin-left: 10px;
    }

    .cart-count {
      position: absolute;
      top: -6px;
      right: -10px;
      background: red;
      color: white;
      font-size: 11px;
      padding: 2px 6px;
      border-radius: 50%;
    }

    /* ===== KERANJANG MODAL ===== */
    .cart-modal {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 10000;
    }

    .cart-box {
      background: white;
      padding: 20px;
      border-radius: 15px;
      width: 320px;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .cart-item button {
      border: none;
      background: red;
      color: white;
      cursor: pointer;
      border-radius: 50%;
      width: 25px;
      height: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .qty-box {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .qty-box button {
      width: 22px;
      height: 22px;
      border: none;
      border-radius: 50%;
      background: #7a4a2e;
      color: white;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-cart {
      padding: 8px 15px;
      background: var(--coffee);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin: 5px;
    }
  </style>
</head>

<body>
  <header>
    <nav>
      <div class="nav-left">
        <h1>Coffee Shop</h1>
        <ul>
          <li><a href="#home">Home</a></li>
          <li><a href="#menu">Menu</a></li>
          <li><a href="#gallery">Galeri</a></li>
          <li><a href="#about">Tentang</a></li>
          <li><a href="#contact">Kontak</a></li>
        </ul>
      </div>

      <!-- NAVBAR AUTH AREA -->
<div class="auth-area">
  <?php if(isset($_SESSION['user_id'])): ?>
    <span class="user-greeting">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
    <span style="font-size: 12px; color: #777; margin: 0 10px;">
      (Role: <?php echo $_SESSION['role'] ?? 'none'; ?>)
    </span>    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="admin_dashboard.php" class="auth-btn" style="background: #2c3e50;">
        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
    </a>
    <?php endif; ?>
    <a href="dashboard.php" class="auth-btn">Dashboard</a>
    <a href="index.php?logout=true" class="auth-btn outline">Logout</a>
  <?php else: ?>
    <!-- SIMPLE BUTTONS - PASTI BERFUNGSI -->
    <a href="index.php?login=admin" class="auth-btn" style="background: #ff6b6b;">
      <i class="fas fa-user-shield"></i> Login as Admin
    </a>
    
    <!-- Tombol Login -->
    <button class="auth-btn" onclick="showLogin()">Login</button>
    
    <!-- Tombol Daftar -->
    <button class="auth-btn outline" onclick="showRegister()">Daftar</button>
  <?php endif; ?>
  
  <!-- Cart Icon -->
  <div class="cart-icon" id="cartBtn" style="margin-left: 10px;">
    <i class="fa-solid fa-cart-shopping"></i>
    <span class="cart-count" id="cartCount">0</span>
  </div>
</div>
    </nav>
  </header>

  <section id="home" class="hero">
    <div class="hero-content" data-aos="fade-up">
      <h2>Pantai Coffee Shop</h2>
      <p class="hero-subtitle">Nikmati kopi terbaik dengan hembusan angin laut.</p>
      <p>Tempat di mana aroma kopi berpadu dengan ketenangan pantai.  
      Santai, modern, dan penuh rasa.</p>
      <a href="#menu" class="btn">Explore Menu</a>
    </div>
    <div class="wave"></div>
  </section>

    <section id="menu">
<h2 data-aos="fade-up">Signature Menu</h2>
<div class="menu-grid">
  <?php
    // Sambungkan ke database
    include "config_db.php";
    
    // Query untuk mengambil menu dari database
    $sql = "SELECT * FROM menu_items ORDER BY created_at DESC LIMIT 6";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0):
        while($item = mysqli_fetch_assoc($result)):
  ?>
  <div class="menu-item" 
       data-name="<?php echo htmlspecialchars($item['name']); ?>" 
       data-price="<?php echo $item['price']; ?>" 
       data-aos="zoom-in">
      <img src="<?php echo $item['image_url']; ?>" 
           alt="<?php echo htmlspecialchars($item['name']); ?>"
           onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
      <h3><?php echo htmlspecialchars($item['name']); ?></h3>
      <p class="price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
      <p style="font-size:14px; color:#666; margin:10px 0;">
          <?php echo substr(htmlspecialchars($item['description']), 0, 60); ?>...
      </p>
      <button class="add-cart">Tambah ke Keranjang</button>
  </div>
  <?php 
        endwhile;
        mysqli_close($conn);
    else:
        // FALLBACK: Jika database kosong, tampilkan menu default
        $fallback_menu = [
            [
                'name' => 'Caramel Macchiato',
                'price' => 32000,
                'image' => 'https://images.unsplash.com/photo-1561047029-3000c68339ca?auto=format&fit=crop&w=400&q=80',
                'description' => 'Kopi dengan rasa karamel yang manis dan creamy'
            ],
            [
                'name' => 'Mocha Delight',
                'price' => 28000,
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=400&q=80',
                'description' => 'Perpaduan coklat dan kopi yang sempurna'
            ],
            [
                'name' => 'Americano Classic',
                'price' => 22000,
                'image' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=400&q=80',
                'description' => 'Kopi hitam klasik yang kuat dan menyegarkan'
            ],
            [
                'name' => 'Vanilla Cold Brew',
                'price' => 27000,
                'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=400&q=80',
                'description' => 'Cold brew dengan esens vanilla yang lembut'
            ],
            [
                'name' => 'Hazelnut Latte',
                'price' => 30000,
                'image' => 'https://images.unsplash.com/photo-1511537190424-bbbab87ac5eb?auto=format&fit=crop&w=400&q=80',
                'description' => 'Latte dengan rasa hazelnut yang khas'
            ],
            [
                'name' => 'Iced Matcha Coffee',
                'price' => 33000,
                'image' => 'https://images.unsplash.com/photo-1567241566621-17c3c5c97b8c?auto=format&fit=crop&w=400&q=80',
                'description' => 'Perpaduan matcha dan kopi yang unik'
            ]
        ];
        
        foreach ($fallback_menu as $index => $item):
  ?>
  <div class="menu-item" 
       data-name="<?php echo $item['name']; ?>" 
       data-price="<?php echo $item['price']; ?>" 
       data-aos="zoom-in" 
       data-aos-delay="<?php echo ($index + 1) * 100; ?>">
      <img src="<?php echo $item['image']; ?>" 
           alt="<?php echo $item['name']; ?>">
      <h3><?php echo $item['name']; ?></h3>
      <p class="price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
      <p style="font-size:14px; color:#666; margin:10px 0;">
          <?php echo $item['description']; ?>
      </p>
      <button class="add-cart">Tambah ke Keranjang</button>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div style="text-align:center; margin-top:50px;">
  <a href="menu.php" class="btn">Lihat Menu Lengkap</a>
</div>

  <section id="gallery">
    <h2 data-aos="fade-up">Coffee & Coastal Moments</h2>
    <div class="gallery">
      <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=700&q=80">
      <img src="https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=700&q=80">
      <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=700&q=80">
      <img src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=700&q=80">
    </div>
  </section>

  <section id="about">
    <a href="about.html">
      <h2 data-aos="fade-up">Our Story</h2>
      <p class="about-text" data-aos="fade-up" data-aos-delay="150">
        Pantai Vibes Coffee hadir untuk kamu yang mencari ketenangan,  
        rasa berkualitas, dan suasana modern yang nyaman. Kami terinspirasi  
        oleh kesejukan pantai dan hangatnya aroma kopi — menciptakan tempat  
        di mana setiap tegukan membawa kedamaian dan cerita.
      </p>
      <div class="testimonial" data-aos="flip-up">
        "Sekali datang, rasanya ingin kembali lagi."  
        <br>— Coffee Lovers
      </div>
    </a>
  </section>
<section id="contact">
    <h2 data-aos="fade-up">Reservasi & Kontak</h2>
    <div class="reservation">
      <form data-aos="fade-up" data-aos-delay="200" id="bookingForm">
        <input type="text" placeholder="Nama Lengkap" required id="nama">
        <input type="email" placeholder="Email Aktif" required id="email">
        <input type="date" required id="tanggal">
        <input type="time" required id="waktu">
        <button type="button" id="whatsappButton">Book Your Table</button>
      </form>

      <script>
        document.getElementById('whatsappButton').addEventListener('click', function() {
          // Ambil nilai dari form
          const nama = document.getElementById('nama').value || '[Nama belum diisi]';
          const email = document.getElementById('email').value || '[Email belum diisi]';
          const tanggal = document.getElementById('tanggal').value || '[Tanggal belum dipilih]';
          const waktu = document.getElementById('waktu').value || '[Waktu belum dipilih]';
          
          // Validasi sederhana
          if (!nama || !email || !tanggal || !waktu) {
            alert('Harap lengkapi semua data terlebih dahulu!');
            return;
          }
          
          // Format pesan untuk WhatsApp
          const pesan = `Halo, saya ingin booking meja:%0A%0A` +
                        `📋 *DATA PEMESANAN*%0A` +
                        `Nama: ${encodeURIComponent(nama)}%0A` +
                        `Email: ${encodeURIComponent(email)}%0A` +
                        `Tanggal: ${encodeURIComponent(tanggal)}%0A` +
                        `Waktu: ${encodeURIComponent(waktu)}%0A%0A` +
                        `Silakan konfirmasi ketersediaan meja. Terima kasih!`;
          
          // Ganti nomor WhatsApp di sini (tanpa +, kode negara, tanpa spasi)
          const nomorWhatsApp = '6281977482146'; // CONTOH: ganti dengan nomor Anda
          
          // Buka WhatsApp
          window.open(`https://wa.me/${nomorWhatsApp}?text=${pesan}`, '_blank');
        });
      </script>
      <div class="social-icons" data-aos="fade-up" data-aos-delay="300">
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-x-twitter"></i></a>
        <a href="#"><i class="fab fa-whatsapp"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
        <a href="#"><i class="fab fa-linkedin"></i></a>
      </div>
    </div>
  </section>

  <footer>&copy; 2025 Coffee Shop — Brewed with Passion</footer>

<!-- LOGIN MODAL -->
<div class="login-modal" id="loginModal">
  <div class="login-box">
    <h2>Login</h2>
    
    <?php if(isset($error)): ?>
      <div style="color: red; background: #ffeaea; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <!-- FORM LOGIN TAPI ACTION KE INDEX.PHP SENDIRI -->
    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username atau Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    
    <p style="margin-top: 10px; font-size: 14px;">
      Belum punya akun? <a href="#" onclick="closeLogin(); openRegister();">Daftar di sini</a>
    </p>
    
    <div class="close-login" onclick="closeLogin()">Tutup</div>
  </div>
</div>

<!-- REGISTER MODAL -->
<div class="login-modal" id="registerModal">
  <div class="login-box">
    <h2>Daftar</h2>
    
    <?php if(isset($_SESSION['errors'])): ?>
      <div style="color: red; background: #ffeaea; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
        <?php 
          foreach($_SESSION['errors'] as $error) {
            echo "<p>$error</p>";
          }
          unset($_SESSION['errors']); 
        ?>
      </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
      <div style="color: green; background: #eaffea; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
        <p><?php echo $_SESSION['success']; ?></p>
        <?php unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>
    
    <form action="register_proses.php" method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="text" name="phone" placeholder="No HP" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
      <button type="submit">Daftar</button>
    </form>
    
    <p style="margin-top: 10px; font-size: 14px;">
      Sudah punya akun? <a href="#" onclick="closeRegister(); openLogin();">Login di sini</a>
    </p>
    
    <div class="close-login" onclick="closeRegister()">Tutup</div>
  </div>
</div>
  <!-- CART MODAL -->
  <div class="cart-modal" id="cartModal">
    <div class="cart-box">
      <h3>Keranjang</h3>
      <div id="cartItems"></div>
      <hr>
      <p><strong>Total: Rp <span id="cartTotal">0</span></strong></p>
      <button class="btn-cart" onclick="checkout()">Checkout</button>
      <button class="btn-cart" onclick="closeCart()">Tutup</button>
    </div>
  </div>

    <!-- SCRIPT AOS -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <!-- SCRIPT UTAMA -->
  <script>
    // Inisialisasi AOS
    AOS.init({ duration: 1000, once: true, easing: "ease-in-out" });

    // ===== CART SYSTEM =====
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    // Update cart count
    function updateCartCount() {
      const cartCount = document.getElementById("cartCount");
      if (!cartCount) return;
      let total = 0;
      cart.forEach(item => total += item.qty);
      cartCount.innerText = total;
    }

    // Initialize cart count
    updateCartCount();

    // Add to cart functionality
    document.querySelectorAll(".add-cart").forEach(btn => {
      btn.addEventListener("click", () => {
        <?php if(!isset($_SESSION['user_id'])): ?>
          alert("Silakan login terlebih dahulu");
          window.location.href = "index.php?login=success";
          return;
        <?php endif; ?>

        const item = btn.closest(".menu-item");
        const name = item.dataset.name;
        const price = parseInt(item.dataset.price);

        if (!name || !price) {
          alert("Data menu belum lengkap");
          return;
        }

        const existing = cart.find(i => i.name === name);

        if (existing) {
          existing.qty += 1;
        } else {
          cart.push({ name, price, qty: 1 });
        }

        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartCount();
        alert(name + " ditambahkan ke keranjang");
      });
    });

    // Cart modal functions
    const cartBtn = document.getElementById("cartBtn");
    const cartModal = document.getElementById("cartModal");

    if (cartBtn) {
      cartBtn.addEventListener("click", () => {
        <?php if(!isset($_SESSION['user_id'])): ?>
          alert("Silakan login terlebih dahulu");
          window.location.href = "index.php?login=success";
          return;
        <?php endif; ?>
        
        renderCart();
        cartModal.style.display = "flex";
      });
    }

    function closeCart() {
      cartModal.style.display = "none";
    }

    function renderCart() {
      const cartItems = document.getElementById("cartItems");
      const cartTotal = document.getElementById("cartTotal");

      if (!cartItems || !cartTotal) return;

      cartItems.innerHTML = "";
      let total = 0;

      if (cart.length === 0) {
        cartItems.innerHTML = "<p>Keranjang kosong</p>";
        cartTotal.innerText = "0";
        return;
      }

      cart.forEach((item, index) => {
        const subtotal = item.price * item.qty;
        total += subtotal;

        cartItems.innerHTML += `
          <div class="cart-item">
            <span>${item.name}</span>
            <div class="qty-box">
              <button onclick="decreaseQty(${index})">−</button>
              <span>${item.qty}</span>
              <button onclick="increaseQty(${index})">+</button>
            </div>
            <span>Rp ${subtotal.toLocaleString()}</span>
            <button onclick="removeItem(${index})">🗑️</button>
          </div>
        `;
      });

      cartTotal.innerText = total.toLocaleString();
    }

    function increaseQty(index) {
      cart[index].qty++;
      localStorage.setItem("cart", JSON.stringify(cart));
      updateCartCount();
      renderCart();
    }

    function decreaseQty(index) {
      if (cart[index].qty > 1) {
        cart[index].qty--;
      } else {
        cart.splice(index, 1);
      }
      localStorage.setItem("cart", JSON.stringify(cart));
      updateCartCount();
      renderCart();
    }

    function removeItem(index) {
      cart.splice(index, 1);
      localStorage.setItem("cart", JSON.stringify(cart));
      updateCartCount();
      renderCart();
    }

    function checkout() {
    if (cart.length === 0) {
        alert("Keranjang masih kosong");
        return;
    }

    let summary = "Pesanan Anda:\n\n";
    let total = 0;

    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        summary += `${item.name} x ${item.qty} = Rp ${subtotal.toLocaleString()}\n`;
    });

    summary += `\nTotal: Rp ${total.toLocaleString()}`;
    
    // Tampilkan modal checkout dengan pilihan
    showCheckoutModal(total, summary);
}

function showCheckoutModal(total, summary) {
    // Buat modal checkout
    const modalHTML = `
        <div class="checkout-modal" id="checkoutModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:10001;">
            <div class="checkout-box" style="background:white; padding:30px; border-radius:20px; width:400px; max-width:90%;">
                <h3 style="color:var(--coffee); margin-top:0;">Checkout Pesanan</h3>
                
                <div style="margin-bottom:20px; background:#f9f9f9; padding:15px; border-radius:10px;">
                    <h4 style="margin-top:0;">Ringkasan Pesanan:</h4>
                    <div id="orderSummary" style="max-height:150px; overflow-y:auto; margin-bottom:10px;">
                        ${summary.replace(/\n/g, '<br>')}
                    </div>
                    <p style="font-weight:bold; text-align:right;">Total: Rp ${total.toLocaleString()}</p>
                </div>
                
                <form id="checkoutForm">
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Nama Pemesan:</label>
                        <input type="text" id="customerName" value="<?php echo $_SESSION['username'] ?? ''; ?>" 
                               style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" required>
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Tipe Pesanan:</label>
                        <div style="display:flex; gap:10px;">
                            <label style="display:flex; align-items:center; gap:5px;">
                                <input type="radio" name="orderType" value="dine_in" checked> 
                                Dine In
                            </label>
                            <label style="display:flex; align-items:center; gap:5px;">
                                <input type="radio" name="orderType" value="takeaway"> 
                                Takeaway
                            </label>
                        </div>
                    </div>
                    
                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Metode Pembayaran:</label>
                        <div style="display:flex; gap:10px;">
                            <label style="display:flex; align-items:center; gap:5px;">
                                <input type="radio" name="paymentMethod" value="cash" checked> 
                                Tunai
                            </label>
                            <label style="display:flex; align-items:center; gap:5px;">
                                <input type="radio" name="paymentMethod" value="qris"> 
                                QRIS
                            </label>
                        </div>
                        
                        <div id="qrisCode" style="display:none; margin-top:10px; text-align:center;">
                            <div style="background:#f0f0f0; padding:15px; border-radius:10px; margin:10px 0;">
                                <p style="margin:0 0 10px 0; font-weight:600;">Scan QR Code untuk Bayar:</p>
                                <div style="background:white; padding:10px; display:inline-block; border-radius:5px;">
                                    <!-- QRIS Code placeholder -->
                                    <div style="width:150px; height:150px; background:#eee; display:flex; align-items:center; justify-content:center; margin:0 auto;">
                                        <span style="color:#666;">[QRIS CODE]</span>
                                    </div>
                                </div>
                                <p style="font-size:12px; color:#666; margin-top:10px;">Total: Rp ${total.toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Catatan (opsional):</label>
                        <textarea id="orderNotes" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; min-height:60px;"></textarea>
                    </div>
                    
                    <div style="display:flex; gap:10px;">
                        <button type="button" onclick="processCheckout(${total})" 
                                style="flex:1; padding:12px; background:var(--coffee); color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                            Konfirmasi Pesanan
                        </button>
                        <button type="button" onclick="closeCheckoutModal()" 
                                style="flex:1; padding:12px; background:#e0e0e0; color:#333; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Tambahkan modal ke body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Tampilkan QRIS jika metode pembayaran QRIS dipilih
    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const qrisDiv = document.getElementById('qrisCode');
            if (this.value === 'qris') {
                qrisDiv.style.display = 'block';
            } else {
                qrisDiv.style.display = 'none';
            }
        });
    });
}

function closeCheckoutModal() {
    const modal = document.getElementById('checkoutModal');
    if (modal) {
        modal.remove();
    }
    closeCart();
}

function processCheckout(total) {
    // Ambil data dari form
    const customerName = document.getElementById('customerName')?.value || '<?php echo $_SESSION['username'] ?? "Pelanggan"; ?>';
    const orderType = document.querySelector('input[name="orderType"]:checked')?.value || 'dine_in';
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'cash';
    const orderNotes = document.getElementById('orderNotes')?.value || '';
    
    if (!customerName.trim()) {
        alert("Mohon isi nama pemesan");
        return;
    }
    
    // Pastikan cart ada
    if (cart.length === 0) {
        alert("Keranjang kosong");
        return;
    }
    
    // Kirim data ke server
    const orderData = {
        customer_name: customerName,
        order_type: orderType,
        payment_method: paymentMethod,
        notes: orderNotes,
        total_amount: total,
        items: cart
    };
    
    console.log("Sending order data:", orderData);
    
    // Kirim ke server via AJAX
    fetch('process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log("Order response:", data);
        if (data.success) {
            alert("✅ Pesanan berhasil dibuat!\nNo. Pesanan: " + data.order_number + "\nTotal: Rp " + total.toLocaleString());
            
            // Reset keranjang
            cart = [];
            localStorage.removeItem("cart");
            updateCartCount();
            
            // Tutup semua modal
            closeCheckoutModal();
            closeCart();
            
            // Optionally redirect to order confirmation page
            // window.location.href = "order_confirmation.php?id=" + data.order_id;
        } else {
            alert("❌ Gagal membuat pesanan: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("⚠️ Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.");
    });
}
    // Close modal ketika klik di luar
    window.onclick = function(event) {
      const loginModal = document.getElementById('loginModal');
      const registerModal = document.getElementById('registerModal');
      const cartModal = document.getElementById('cartModal');
      
      if (event.target == loginModal) {
        closeLogin();
      }
      if (event.target == registerModal) {
        closeRegister();
      }
      if (event.target == cartModal) {
        closeCart();
      }
    }
  </script>

  <script>
// FUNGSI SEDERHANA UNTUK MODAL
function showLogin() {
    console.log('Show login modal');
    document.getElementById('loginModal').style.display = 'flex';
}

function showRegister() {
    console.log('Show register modal');
    document.getElementById('registerModal').style.display = 'flex';
}

function closeLogin() {
    document.getElementById('loginModal').style.display = 'none';
}

function closeRegister() {
    document.getElementById('registerModal').style.display = 'none';
}

// TUTUP MODAL KETIKA KLIK DI LUAR
window.onclick = function(event) {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const cartModal = document.getElementById('cartModal');
    
    if (event.target === loginModal) {
        loginModal.style.display = 'none';
    }
    if (event.target === registerModal) {
        registerModal.style.display = 'none';
    }
    if (event.target === cartModal) {
        cartModal.style.display = 'none';
    }
}

// TAMBAH EVENT LISTENER SETELAH DOM LOADED
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Loaded - Modals ready');
    
    // Debug: cek apakah modal ada
    console.log('Login modal exists:', !!document.getElementById('loginModal'));
    console.log('Register modal exists:', !!document.getElementById('registerModal'));
    
    // Tambah event listener untuk link di dalam modal
    const loginLink = document.querySelector('a[href="#"][onclick*="openLogin"]');
    const registerLink = document.querySelector('a[href="#"][onclick*="openRegister"]');
    
    if (loginLink) {
        loginLink.onclick = function(e) {
            e.preventDefault();
            closeRegister();
            showLogin();
        };
    }
    
    if (registerLink) {
        registerLink.onclick = function(e) {
            e.preventDefault();
            closeLogin();
            showRegister();
        };
    }
    
    // Event listener untuk tombol Tutup
    const closeButtons = document.querySelectorAll('.close-login');
    closeButtons.forEach(btn => {
        if (btn.textContent.includes('Tutup')) {
            btn.onclick = function() {
                if (document.getElementById('loginModal').style.display === 'flex') {
                    closeLogin();
                } else if (document.getElementById('registerModal').style.display === 'flex') {
                    closeRegister();
                }
            };
        }
    });
});
</script>
</body>
</html>
