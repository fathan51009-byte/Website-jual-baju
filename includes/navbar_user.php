<?php
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) { $cart_count += $item['quantity']; }
}
$base = (strpos($_SERVER['PHP_SELF'], '/user/') !== false) ? '' : 'user/';
?>
<nav class="navbar">
  <div class="container navbar-inner">
    <a href="dashboard.php" class="navbar-brand">
      <span></span> Toko Baju
    </a>
    <ul class="navbar-links">
      <li><a href="dashboard.php">Katalog</a></li>
      <li>
        <a href="cart.php">
          🛒 Keranjang
          <?php if ($cart_count > 0): ?>
            <span class="nav-badge"><?= $cart_count ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li><a href="orders.php">Pesanan Saya</a></li>
    </ul>
    <div class="navbar-user">
      👤 <?= htmlspecialchars($_SESSION['username']) ?> &nbsp;|&nbsp;
      <a href="../logout.php">Keluar</a>
    </div>
  </div>
</nav>
