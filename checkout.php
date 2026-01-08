<?php
include "config/db.php";
if (!isset($_SESSION['user_id'])) {
  header("Location: auth/login.php");
  exit;
}
$user_id = $_SESSION['user_id'];

mysqli_query($conn, "DELETE FROM cart WHERE user_id='$user_id'");
echo "Checkout berhasil";
