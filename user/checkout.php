<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../index.php"); exit(); }
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) { header("Location: cart.php"); exit(); }
include_once '../includes/koneksi.php';
$error = ''; $total = 0;
foreach ($cart as $item) { $total += $item['harga'] * $item['quantity']; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alamat = trim($_POST['alamat']);
    if (empty($alamat)) { $error = "Alamat pengiriman tidak boleh kosong."; }
    else {
        mysqli_begin_transaction($conn);
        try {
            $uid = $_SESSION['user_id'];
            $stmt_o = mysqli_prepare($conn,"INSERT INTO orders (user_id,total_harga,alamat,status) VALUES (?,?,'pending')");
            // need 3 params: user_id, total, alamat
            $stmt_o = mysqli_prepare($conn,"INSERT INTO orders (user_id,total_harga,alamat,status) VALUES (?,?,?,'pending')");
            mysqli_stmt_bind_param($stmt_o,"ids",$uid,$total,$alamat);
            mysqli_stmt_execute($stmt_o);
            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_o);
            foreach ($cart as $pid => $item) {
                $stmt_s = mysqli_prepare($conn,"SELECT stok FROM products WHERE id=?");
                mysqli_stmt_bind_param($stmt_s,"i",$pid);
                mysqli_stmt_execute($stmt_s);
                mysqli_stmt_bind_result($stmt_s,$stok); mysqli_stmt_fetch($stmt_s); mysqli_stmt_close($stmt_s);
                if ($stok < $item['quantity']) throw new Exception("Stok ".htmlspecialchars($item['nama_produk'])." tidak mencukupi.");
                $stmt_i = mysqli_prepare($conn,"INSERT INTO order_items (order_id,product_id,quantity,harga) VALUES (?,?,?,?)");
                mysqli_stmt_bind_param($stmt_i,"iiid",$order_id,$pid,$item['quantity'],$item['harga']);
                mysqli_stmt_execute($stmt_i); mysqli_stmt_close($stmt_i);
                $stmt_u = mysqli_prepare($conn,"UPDATE products SET stok=stok-? WHERE id=?");
                mysqli_stmt_bind_param($stmt_u,"ii",$item['quantity'],$pid);
                mysqli_stmt_execute($stmt_u); mysqli_stmt_close($stmt_u);
            }
            mysqli_commit($conn);
            unset($_SESSION['cart']);
            header("Location: orders.php?success=1"); exit();
        } catch (Exception $e) { mysqli_rollback($conn); $error = "Checkout gagal: ".$e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout — Toko Baju Online</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">
<?php include_once '../includes/navbar_user.php'; ?>
<div class="main-content">
<div class="container" style="max-width:800px;">

  <div class="page-header">
    <a href="cart.php" class="btn btn-ghost btn-sm" style="margin-bottom:12px;">← Kembali ke Keranjang</a>
    <h1>Checkout</h1>
    <p>Periksa pesanan Anda sebelum konfirmasi</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start;">

    <!-- Alamat -->
    <div class="card">
      <h2 style="margin-bottom:20px; font-size:1.1rem;">Informasi Pengiriman</h2>
      <form action="checkout.php" method="POST" id="checkout-form">
        <div class="form-group">
          <label>Nama Penerima</label>
          <input class="form-control" type="text" placeholder="<?= htmlspecialchars($_SESSION['username']) ?>" disabled style="background:var(--neutral-50);">
        </div>
        <div class="form-group">
          <label>Alamat Pengiriman Lengkap *</label>
          <textarea class="form-control" name="alamat" rows="4"
                    placeholder="Jl. Contoh No. 1, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos"
                    required><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
        </div>
        <button type="submit" form="checkout-form" class="btn btn-primary btn-full">Konfirmasi Pesanan</button>
      </form>
    </div>

    <!-- Ringkasan -->
    <div>
      <div class="card">
        <h2 style="margin-bottom:16px; font-size:1.1rem;">Ringkasan Pesanan</h2>
        <?php foreach ($cart as $item): ?>
        <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:0.875rem;">
          <div>
            <div style="font-weight:600; color:var(--neutral-800);"><?= htmlspecialchars($item['nama_produk']) ?></div>
            <div class="text-muted">× <?= $item['quantity'] ?></div>
          </div>
          <div style="color:var(--purple-700); font-weight:500;">
            Rp <?= number_format($item['harga']*$item['quantity'],0,',','.') ?>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="border-top:1px solid var(--purple-100); padding-top:14px; margin-top:4px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span class="text-muted text-sm">Total</span>
            <span style="font-family:'DM Serif Display',serif; font-size:1.4rem; color:var(--purple-700);">
              Rp <?= number_format($total,0,',','.') ?>
            </span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</div>
<div class="footer"><div class="container">© 2024 Toko Baju Online</div></div>
</body>
</html>
