<?php
// process_order.php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

include "config_db.php";

// Ambil data dari POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit();
}

// Generate nomor pesanan unik
$order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

// Cek apakah nomor pesanan sudah ada (sangat kecil kemungkinannya)
$check_sql = "SELECT id FROM orders WHERE order_number = '$order_number'";
$check_result = mysqli_query($conn, $check_sql);
if (mysqli_num_rows($check_result) > 0) {
    // Jika ada, generate nomor baru
    $order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
}

// Simpan pesanan ke database
$user_id = $_SESSION['user_id'];
$customer_name = mysqli_real_escape_string($conn, $input['customer_name'] ?? '');
$order_type = mysqli_real_escape_string($conn, $input['order_type'] ?? 'dine_in');
$payment_method = mysqli_real_escape_string($conn, $input['payment_method'] ?? 'cash');
$notes = mysqli_real_escape_string($conn, $input['notes'] ?? '');
$total_amount = intval($input['total_amount'] ?? 0);

// Debug log
error_log("Processing order: $order_number, Customer: $customer_name, Total: $total_amount");

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Insert ke tabel orders - PERHATIKAN URUTAN KOLOM
    $sql_order = "INSERT INTO orders (order_number, user_id, customer_name, order_type, payment_method, total_amount, notes, status) 
                  VALUES ('$order_number', '$user_id', '$customer_name', '$order_type', '$payment_method', '$total_amount', '$notes', 'pending')";
    
    error_log("SQL Order: " . $sql_order);
    
    if (!mysqli_query($conn, $sql_order)) {
        throw new Exception("Gagal menyimpan pesanan: " . mysqli_error($conn));
    }
    
    $order_id = mysqli_insert_id($conn);
    error_log("Order created with ID: $order_id");
    
    // Insert item pesanan
    if (isset($input['items']) && is_array($input['items'])) {
        foreach ($input['items'] as $item) {
            $menu_name = mysqli_real_escape_string($conn, $item['name'] ?? '');
            $quantity = intval($item['qty'] ?? 1);
            $price = intval($item['price'] ?? 0);
            $subtotal = $quantity * $price;
            
            $sql_item = "INSERT INTO order_items (order_id, menu_name, quantity, price, subtotal) 
                         VALUES ('$order_id', '$menu_name', '$quantity', '$price', '$subtotal')";
            
            error_log("SQL Item: " . $sql_item);
            
            if (!mysqli_query($conn, $sql_item)) {
                throw new Exception("Gagal menyimpan item pesanan: " . mysqli_error($conn));
            }
        }
    }
    
    // Commit transaksi
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil dibuat',
        'order_number' => $order_number,
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    error_log("Order processing error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>