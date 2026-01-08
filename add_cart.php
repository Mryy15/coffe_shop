<?php
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$price = $_POST['price'];

$cek = mysqli_query($conn,
  "SELECT * FROM cart WHERE user_id='$user_id' AND name='$name'"
);

if (mysqli_num_rows($cek) > 0) {
  mysqli_query($conn,
    "UPDATE cart SET qty = qty + 1 WHERE user_id='$user_id' AND name='$name'"
  );
} else {
  mysqli_query($conn,
    "INSERT INTO cart (user_id, name, price, qty)
     VALUES ('$user_id', '$name', '$price', 1)"
  );
}

header("Location: cart.php");
