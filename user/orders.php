<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../index.php"); exit(); }
include_once '../includes/koneksi.php';
$uid = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn,"SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt,"i",$uid);
mysqli_stmt_execute($stmt);
$result_orders = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya — Toko Baju Online</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">
<?php include_once '../includes/navbar_user.php'; ?>
<div class="main-content">
<div class="container" style="max-width:800px;">

  <div class="page-header">
    <h1>Pesanan Saya</h1>
    <p>Riwayat semua pesanan yang telah Anda buat</p>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✓ Pesanan berhasil dibuat! Kami akan segera memprosesnya.</div>
  <?php endif; ?>

  <?php if (mysqli_num_rows($result_orders) > 0): ?>
    <?php while ($order = mysqli_fetch_assoc($result_orders)): ?>
    <div class="order-card">
      <div class="order-card-header">
        <span class="order-id">Pesanan #<?= $order['id'] ?></span>
        <div style="display:flex; align-items:center; gap:12px;">
          <span class="text-muted text-sm"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></span>
          <span class="badge badge-<?= $order['status'] ?>"><?= $order['status'] ?></span>
        </div>
      </div>
      <div class="order-card-body">
        <?php
        $stmt_items = mysqli_prepare($conn,
            "SELECT oi.*, p.nama_produk FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
        mysqli_stmt_bind_param($stmt_items,"i",$order['id']);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);
        ?>
        <table style="width:100%; font-size:0.875rem; margin-bottom:14px;">
          <?php while ($item = mysqli_fetch_assoc($result_items)): ?>
          <tr>
            <td style="padding:5px 0; color:var(--neutral-700);"><?= htmlspecialchars($item['nama_produk']) ?></td>
            <td style="padding:5px 0; color:var(--neutral-400); text-align:center;">× <?= $item['quantity'] ?></td>
            <td style="padding:5px 0; color:var(--purple-700); text-align:right;">
              Rp <?= number_format($item['harga']*$item['quantity'],0,',','.') ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid var(--purple-100);">
          <div class="text-sm text-muted">📍 <?= htmlspecialchars($order['alamat']) ?></div>
          <div style="font-family:'DM Serif Display',serif; font-size:1.15rem; color:var(--purple-700);">
            Rp <?= number_format($order['total_harga'],0,',','.') ?>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  <?php else: ?>
  <div class="empty-state">
    <div class="empty-icon">📋</div>
    <p>Anda belum memiliki pesanan.</p>
    <a href="dashboard.php" class="btn btn-primary">Mulai Belanja</a>
  </div>
  <?php endif; ?>

</div>
</div>
<div class="footer"><div class="container">© 2024 Toko Baju Online</div></div>
</body>
</html>
