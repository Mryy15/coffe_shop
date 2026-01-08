<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Setup Database Coffee Shop</h2>";

// Koneksi ke MySQL (tanpa database)
$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("❌ Koneksi MySQL gagal: " . mysqli_connect_error());
}

echo "✅ Koneksi MySQL berhasil<br>";

// Buat database jika belum ada
$dbname = "coffee_shop_db";
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "✅ Database '$dbname' berhasil dibuat/tersedia<br>";
} else {
    echo "❌ Gagal membuat database: " . mysqli_error($conn) . "<br>";
}

// Pilih database
mysqli_select_db($conn, $dbname);

// Buat tabel users
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql_users)) {
    echo "✅ Tabel 'users' berhasil dibuat<br>";
} else {
    echo "❌ Gagal membuat tabel users: " . mysqli_error($conn) . "<br>";
}

// Buat tabel orders
$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    items TEXT NOT NULL,
    total_price INT NOT NULL,
    status ENUM('pending','processing','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql_orders)) {
    echo "✅ Tabel 'orders' berhasil dibuat<br>";
} else {
    echo "❌ Gagal membuat tabel orders: " . mysqli_error($conn) . "<br>";
}

// Buat user admin (password: admin123)
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$sql_admin = "INSERT IGNORE INTO users (username, phone, email, password, role) 
              VALUES ('admin', '08123456789', 'admin@coffee.com', '$admin_pass', 'admin')";

if (mysqli_query($conn, $sql_admin)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo "✅ User admin berhasil dibuat (username: admin, password: admin123)<br>";
    } else {
        echo "⚠️ User admin sudah ada<br>";
    }
} else {
    echo "❌ Gagal membuat user admin: " . mysqli_error($conn) . "<br>";
}

// Test insert data user biasa
$test_pass = password_hash('test123', PASSWORD_DEFAULT);
$sql_test = "INSERT IGNORE INTO users (username, phone, email, password) 
             VALUES ('testuser', '08111111111', 'test@coffee.com', '$test_pass')";

if (mysqli_query($conn, $sql_test)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo "✅ User test berhasil dibuat (username: testuser, password: test123)<br>";
    } else {
        echo "⚠️ User test sudah ada<br>";
    }
}

echo "<hr>";
echo "<h3>Database siap digunakan!</h3>";
echo "<a href='index.html'>Kembali ke Home</a>";

mysqli_close($conn);
?>