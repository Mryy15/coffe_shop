<?php
include "config/db.php";
if (!isset($_SESSION['user_id'])) {
  header("Location: auth/login.php");
}
$user_id = $_SESSION['user_id'];

$q = mysqli_query($conn, "SELECT * FROM cart WHERE user_id='$user_id'");
$total = 0;
?>

<h2>Keranjang</h2>

<?php while ($c = mysqli_fetch_assoc($q)) {
  $subtotal = $c['price'] * $c['qty'];
  $total += $subtotal;
?>
<div>
  <?= $c['name'] ?> |
  Rp <?= number_format($c['price']) ?> |
  Qty: <?= $c['qty'] ?>

  <a href="update_cart.php?id=<?= $c['id'] ?>&aksi=plus">+</a>
  <a href="update_cart.php?id=<?= $c['id'] ?>&aksi=minus">-</a>
  <a href="update_cart.php?id=<?= $c['id'] ?>&aksi=hapus">❌</a>
</div>
<?php } ?>

<h3>Total: Rp <?= number_format($total) ?></h3>

<a href="checkout.php">Checkout</a>
