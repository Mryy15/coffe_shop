<?php
// fix_database.php
include "config_db.php";

echo "<h2>Memperbaiki Database...</h2>";

$sqls = [
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_number VARCHAR(50) UNIQUE AFTER id",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS customer_name VARCHAR(100) AFTER user_id",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_type VARCHAR(20) DEFAULT 'dine_in'",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(20) DEFAULT 'cash'",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'pending'",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS notes TEXT",
    "UPDATE orders SET order_number = CONCAT('ORD-', LPAD(id, 6, '0')) WHERE order_number IS NULL",
];

foreach ($sqls as $sql) {
    echo "<p>Executing: $sql</p>";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>✓ Success</p>";
    } else {
        echo "<p style='color:red'>✗ Error: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>Database berhasil diperbaiki!</h3>";
echo "<a href='index.php'>Kembali ke Home</a>";

mysqli_close($conn);
?>