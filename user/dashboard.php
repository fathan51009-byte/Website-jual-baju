<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../index.php"); exit(); }
include_once '../includes/koneksi.php';
$per_page = 6;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1)*$per_page;
$total_rows = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM products WHERE stok > 0"))['t'];
$total_pages = ceil($total_rows/$per_page);
$stmt = mysqli_prepare($conn,"SELECT * FROM products WHERE stok > 0 ORDER BY id DESC LIMIT ? OFFSET ?");
mysqli_stmt_bind_param($stmt,"ii",$per_page,$offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Katalog — Toko Baju Online</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">
<?php include_once '../includes/navbar_user.php'; ?>
<div class="main-content">
<div class="container">

  <div class="page-header">
    <h1>Katalog Produk</h1>
    <p>Temukan koleksi pakaian terbaik pilihan kami</p>
  </div>

  <?php if (mysqli_num_rows($result) > 0): ?>
  <div class="product-grid">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="product-card">
      <?php if ($row['gambar']): ?>
        <img class="product-card-img" src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
      <?php else: ?>
        <div class="product-card-img-placeholder">👕</div>
      <?php endif; ?>

      <div class="product-card-body">
        <div class="product-card-name"><?= htmlspecialchars($row['nama_produk']) ?></div>
        <?php if ($row['deskripsi']): ?>
          <div class="product-card-desc"><?= htmlspecialchars(substr($row['deskripsi'],0,72)) ?>...</div>
        <?php endif; ?>
        <div class="stok-badge">Stok: <?= $row['stok'] ?></div>
        <div class="product-card-price">Rp <?= number_format($row['harga'],0,',','.') ?></div>
        <form action="add_to_cart.php" method="POST">
          <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
          <input type="hidden" name="nama_produk" value="<?= htmlspecialchars($row['nama_produk']) ?>">
          <input type="hidden" name="harga" value="<?= $row['harga'] ?>">
          <div class="product-card-footer">
            <input type="number" name="quantity" value="1" min="1" max="<?= $row['stok'] ?>">
            <button type="submit" class="btn btn-primary btn-sm" style="flex:1;">+ Keranjang</button>
          </div>
        </form>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <?php if ($total_pages > 1): ?>
  <div class="pagination">
    <?php for ($i=1; $i<=$total_pages; $i++): ?>
      <?php if ($i === $page): ?>
        <span class="active"><?= $i ?></span>
      <?php else: ?>
        <a href="dashboard.php?page=<?= $i ?>"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="empty-state">
    <div class="empty-icon">👕</div>
    <p>Belum ada produk yang tersedia saat ini.</p>
  </div>
  <?php endif; ?>

</div>
</div>
<div class="footer"><div class="container">© 2024 Toko Baju Online</div></div>
</body>
</html>
