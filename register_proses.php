<?php
// register_proses.php
session_start();
include "config_db.php";

$errors = [];

// Validasi input
$username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
$email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
$phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validasi
if (empty($username)) $errors[] = "Username harus diisi";
if (empty($email)) $errors[] = "Email harus diisi";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid";
if (empty($phone)) $errors[] = "Nomor telepon harus diisi";
if (empty($password)) $errors[] = "Password harus diisi";
if ($password !== $confirm_password) $errors[] = "Password tidak cocok";

// Cek apakah username/email sudah terdaftar
if (empty($errors)) {
    $check_sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Username atau email sudah terdaftar";
    }
}

// Jika tidak ada error, simpan ke database
if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = (strtolower($username) === 'admin') ? 'admin' : 'user';
    
    $sql = "INSERT INTO users (username, email, phone, password, role) 
            VALUES ('$username', '$email', '$phone', '$hashed_password', '$role')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        
        // Auto login setelah registrasi
        $user_id = mysqli_insert_id($conn);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        $_SESSION['role'] = $role;
        
        header("Location: dashboard.php");
        exit();
    } else {
        $errors[] = "Gagal menyimpan data: " . mysqli_error($conn);
    }
}

// Jika ada error, simpan ke session dan redirect kembali
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: index.php");
    exit();
}

mysqli_close($conn);
?>