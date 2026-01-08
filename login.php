// login.php
<?php
session_start();
// Tanpa validasi, langsung set session logged in
$_SESSION['logged_in'] = true;
$_SESSION['username'] = 'Pengguna';
header("Location:index.php"); // atau halaman Coffee Shop Anda
exit();
?>