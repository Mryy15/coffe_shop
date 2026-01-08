<?php
include "config/db.php";
$id = $_GET['id'];
$aksi = $_GET['aksi'];

if ($aksi == "plus") {
  mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE id='$id'");
}
elseif ($aksi == "minus") {
  mysqli_query($conn, "UPDATE cart SET qty = qty - 1 WHERE id='$id'");
  mysqli_query($conn, "DELETE FROM cart WHERE id='$id' AND qty <= 0");
}
elseif ($aksi == "hapus") {
  mysqli_query($conn, "DELETE FROM cart WHERE id='$id'");
}

header("Location: cart.php");
