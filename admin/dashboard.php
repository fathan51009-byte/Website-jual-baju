<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit(); }
include_once '../includes/koneksi.php';

$total_produk   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM products"))['t'];
$total_pesanan  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM orders"))['t'];
$pesanan_pending= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM orders WHERE status='pending'"))['t'];
$total_user     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM users WHERE role='user'"))['t'];

$status_msg = '';
if (isset($_GET['status'])) {
    $msgs = ['deleted'=>'Produk berhasil dihapus.','added'=>'Produk berhasil ditambahkan.','updated'=>'Produk berhasil diperbarui.'];
    $status_msg = $msgs[$_GET['status']] ?? '';
}

$result_produk = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$result_orders = mysqli_query($conn, "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin — Toko Baju</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">

<?php include_once '../includes/navbar_admin.php'; ?>

<div class="main-content">
<div class="container">

  <div class="page-header">
    <h1>Dashboard</h1>
    <p>Kelola produk dan pantau pesanan pelanggan</p>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">👕</div>
      <div class="stat-label">Total Produk</div>
      <div class="stat-value"><?= $total_produk ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div class="stat-label">Pelanggan</div>
      <div class="stat-value"><?= $total_user ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📦</div>
      <div class="stat-label">Total Pesanan</div>
      <div class="stat-value"><?= $total_pesanan ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⏳</div>
      <div class="stat-label">Pending</div>
      <div class="stat-value"><?= $pesanan_pending ?></div>
    </div>
  </div>

  <!-- Produk -->
  <div class="section">
    <div class="section-header">
      <h2>Manajemen Produk</h2>
      <a href="tambah_produk.php" class="btn btn-primary btn-sm">+ Tambah Produk</a>
    </div>

    <?php if ($status_msg): ?>
      <div class="alert alert-success">✓ <?= htmlspecialchars($status_msg) ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result_produk) > 0): ?>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Gambar</th>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($result_produk)): ?>
          <tr>
            <td>
              <?php if ($row['gambar']): ?>
                <img class="table-img" src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="">
              <?php else: ?>
                <div class="table-img-placeholder">👕</div>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($row['nama_produk']) ?></strong></td>
            <td class="text-purple font-serif">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
            <td><?= $row['stok'] ?></td>
            <td>
              <div class="action-group">
                <a href="edit_produk.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                <a href="delete_produk.php?id=<?= $row['id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">📦</div>
      <p>Belum ada produk.</p>
      <a href="tambah_produk.php" class="btn btn-primary">+ Tambah Produk Pertama</a>
    </div>
    <?php endif; ?>
  </div>

  <div class="divider"></div>

  <!-- Pesanan -->
  <div class="section">
    <div class="section-header">
      <h2>Manajemen Pesanan</h2>
    </div>

    <?php if (mysqli_num_rows($result_orders) > 0): ?>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Alamat</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($order = mysqli_fetch_assoc($result_orders)): ?>
          <tr>
            <td class="text-muted text-sm"><?= $order['id'] ?></td>
            <td><strong><?= htmlspecialchars($order['username']) ?></strong></td>
            <td class="text-purple font-serif">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
            <td class="text-sm" style="max-width:180px;"><?= htmlspecialchars(substr($order['alamat'],0,60)) ?>...</td>
            <td><span class="badge badge-<?= $order['status'] ?>"><?= $order['status'] ?></span></td>
            <td class="text-sm text-muted"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
            <td>
              <form action="update_order.php" method="POST" class="inline-form">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <select name="status" class="form-control" style="width:auto; padding:6px 30px 6px 10px; font-size:0.8rem;">
                  <option value="pending"  <?= $order['status']==='pending'  ? 'selected':'' ?>>Pending</option>
                  <option value="dikirim"  <?= $order['status']==='dikirim'  ? 'selected':'' ?>>Dikirim</option>
                  <option value="selesai"  <?= $order['status']==='selesai'  ? 'selected':'' ?>>Selesai</option>
                </select>
                <button type="submit" class="btn btn-sm btn-success">Simpan</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">🛍️</div>
      <p>Belum ada pesanan masuk.</p>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>
<div class="footer"><div class="container">© 2024 Toko Baju Online</div></div>
</body>
</html>
