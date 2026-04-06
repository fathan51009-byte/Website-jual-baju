<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../index.php"); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $pid => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) unset($_SESSION['cart'][$pid]);
        elseif (isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid]['quantity'] = $qty;
    }
    header("Location: cart.php"); exit();
}
if (isset($_GET['remove'])) { unset($_SESSION['cart'][(int)$_GET['remove']]); header("Location: cart.php"); exit(); }
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) { $total += $item['harga'] * $item['quantity']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keranjang — Toko Baju Online</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">
<?php include_once '../includes/navbar_user.php'; ?>
<div class="main-content">
<div class="container" style="max-width:800px;">

  <div class="page-header">
    <a href="dashboard.php" class="btn btn-ghost btn-sm" style="margin-bottom:12px;">← Lanjut Belanja</a>
    <h1>Keranjang Belanja</h1>
  </div>

  <?php if (empty($cart)): ?>
  <div class="empty-state">
    <div class="empty-icon">🛒</div>
    <p>Keranjang Anda masih kosong.</p>
    <a href="dashboard.php" class="btn btn-primary">Mulai Belanja</a>
  </div>
  <?php else: ?>

  <form action="cart.php" method="POST">
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Produk</th>
            <th>Harga Satuan</th>
            <th>Jumlah</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart as $pid => $item): ?>
          <tr>
            <td><strong><?= htmlspecialchars($item['nama_produk']) ?></strong></td>
            <td>Rp <?= number_format($item['harga'],0,',','.') ?></td>
            <td>
              <input class="cart-qty-input" type="number" name="quantity[<?= $pid ?>]"
                     value="<?= $item['quantity'] ?>" min="1">
            </td>
            <td class="text-purple font-serif">Rp <?= number_format($item['harga']*$item['quantity'],0,',','.') ?></td>
            <td>
              <a href="cart.php?remove=<?= $pid ?>"
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Hapus item ini?')">✕</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding:20px; background:var(--white); border:1px solid var(--purple-100); border-radius:var(--radius);">
      <div>
        <div class="text-muted text-sm">Total Pembayaran</div>
        <div style="font-family:'DM Serif Display',serif; font-size:1.6rem; color:var(--purple-700);">
          Rp <?= number_format($total,0,',','.') ?>
        </div>
      </div>
      <div style="display:flex; gap:12px;">
        <button type="submit" name="update_cart" class="btn btn-outline">Update</button>
        <a href="checkout.php" class="btn btn-primary">Checkout →</a>
      </div>
    </div>
  </form>

  <?php endif; ?>
</div>
</div>
<div class="footer"><div class="container">© 2024 Toko Baju Online</div></div>
</body>
</html>
