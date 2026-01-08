<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>DEBUG REGISTRASI</h2>";
echo "<pre>";

// 1. Cek apakah form terkirim
echo "1. Cek POST data:\n";
print_r($_POST);
echo "\n\n";

// 2. Simulasikan data POST untuk testing
if (empty($_POST)) {
    echo "⚠️ TIDAK ADA DATA POST! Form mungkin tidak terkirim.\n";
    echo "Cara test: <a href='test_form.html'>Klik di sini untuk form test</a>\n";
    exit();
}

// 3. Cek semua field
$required = ['username', 'phone', 'email', 'password'];
$missing = [];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    echo "❌ Field yang kosong: " . implode(', ', $missing) . "\n";
    exit();
}

echo "✅ Semua field terisi\n\n";

// 4. Test koneksi database
echo "2. Test koneksi database:\n";
include "config_db.php";

if (!$conn) {
    echo "❌ Koneksi database gagal: " . mysqli_connect_error() . "\n";
} else {
    echo "✅ Koneksi database berhasil\n";
    
    // 5. Cek apakah tabel users ada
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($check_table) == 0) {
        echo "❌ Tabel 'users' tidak ditemukan!\n";
        echo "Buat tabel dengan SQL berikut:\n";
        echo "CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user','admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );\n";
    } else {
        echo "✅ Tabel 'users' ditemukan\n";
    }
}

echo "</pre>";
?>