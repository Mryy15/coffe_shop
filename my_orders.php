<?php
// my_orders.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include "config_db.php";

$user_id = $_SESSION['user_id'];

// Ambil semua pesanan user
$sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
$result = mysqli_query($conn, $sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Coffee Shop</title>
    <!-- Copy style dari dashboard.php atau buat sederhana -->
</head>
<body>
    <h1>Pesanan Saya</h1>
    <!-- Tampilkan semua pesanan user -->
</body>
</html>