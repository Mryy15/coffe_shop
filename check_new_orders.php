<?php
// check_new_orders.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['new_orders' => 0]);
    exit();
}

include "config_db.php";

// Hitung pesanan baru (status pending) sejak 5 menit terakhir
$five_minutes_ago = date('Y-m-d H:i:s', strtotime('-5 minutes'));
$sql = "SELECT COUNT(*) as new_orders FROM orders 
        WHERE status = 'pending' AND order_date > '$five_minutes_ago'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($sql);

echo json_encode([
    'new_orders' => $data['new_orders'] ?? 0,
    'timestamp' => date('H:i:s')
]);

mysqli_close($conn);
?>