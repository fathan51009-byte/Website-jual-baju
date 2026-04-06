<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit(); }
include_once '../includes/koneksi.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) { echo "ID tidak valid."; exit(); }
$stmt_sel = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt_sel, "i", $product_id);
mysqli_stmt_execute($stmt_sel);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_sel));
mysqli_stmt_close($stmt_sel);
if (!$product) { echo "Produk tidak ditemukan."; exit(); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']); $harga = $_POST['harga'];
    $stok = $_POST['stok']; $deskripsi = trim($_POST['deskripsi']);
    $gambar_final = $product['gambar'];
    if (empty($nama_produk)||empty($harga)||empty($stok)) { $error = "Nama, harga, dan stok wajib diisi."; }
    elseif (!is_numeric($harga)||$harga<0) { $error = "Harga harus angka positif."; }
    elseif (!is_numeric($stok)||$stok<0) { $error = "Stok harus angka positif."; }
    else {
        if (!empty($_FILES['gambar']['name'])) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($_FILES['gambar']['type'],$allowed)) { $error = "Format gambar tidak didukung."; }
            elseif ($_FILES['gambar']['size']>2*1024*1024) { $error = "Ukuran maks 2MB."; }
            else {
                $ext = pathinfo($_FILES['gambar']['name'],PATHINFO_EXTENSION);
                $gambar_baru = uniqid('prod_').'.'.$ext;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], "../uploads/".$gambar_baru)) {
                    if ($product['gambar'] && file_exists("../uploads/".$product['gambar'])) unlink("../uploads/".$product['gambar']);
                    $gambar_final = $gambar_baru;
                } else { $error = "Gagal upload gambar."; }
            }
        }
        if (empty($error)) {
            $stmt_upd = mysqli_prepare($conn, "UPDATE products SET nama_produk=?,harga=?,stok=?,deskripsi=?,gambar=? WHERE id=?");
            mysqli_stmt_bind_param($stmt_upd,"sdiisi",$nama_produk,$harga,$stok,$deskripsi,$gambar_final,$product_id);
            if (mysqli_stmt_execute($stmt_upd)) { header("Location: dashboard.php?status=updated"); exit(); }
            else { $error = "Gagal memperbarui produk."; }
            mysqli_stmt_close($stmt_upd);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Produk — Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">
<?php include_once '../includes/navbar_admin.php'; ?>
<div class="main-content">
<div class="container" style="max-width:640px;">
  <div class="page-header">
    <a href="dashboard.php" class="btn btn-ghost btn-sm" style="margin-bottom:12px;">← Kembali</a>
    <h1>Edit Produk</h1>
    <p>Perbarui informasi produk</p>
  </div>
  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-danger">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="edit_produk.php?id=<?= $product_id ?>" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>Nama Produk *</label>
        <input class="form-control" type="text" name="nama_produk"
               value="<?= htmlspecialchars($product['nama_produk']) ?>" required>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="form-group">
          <label>Harga (Rp) *</label>
          <input class="form-control" type="number" name="harga" min="0"
                 value="<?= htmlspecialchars($product['harga']) ?>" required>
        </div>
        <div class="form-group">
          <label>Stok *</label>
          <input class="form-control" type="number" name="stok" min="0"
                 value="<?= htmlspecialchars($product['stok']) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea class="form-control" name="deskripsi" rows="3"><?= htmlspecialchars($product['deskripsi']) ?></textarea>
      </div>
      <div class="form-group">
        <label>Gambar Saat Ini</label>
        <?php if ($product['gambar']): ?>
          <div style="margin-bottom:10px;">
            <img src="../uploads/<?= htmlspecialchars($product['gambar']) ?>" style="width:100px; border-radius:8px; border:1px solid var(--purple-100);">
          </div>
        <?php else: ?>
          <p class="text-muted text-sm" style="margin-bottom:10px;">Tidak ada gambar</p>
        <?php endif; ?>
        <label>Ganti Gambar <span class="text-muted text-sm">(kosongkan jika tidak berubah)</span></label>
        <input class="form-control" type="file" name="gambar" accept="image/*">
      </div>
      <div style="display:flex; gap:12px; margin-top:8px;">
        <button type="submit" class="btn btn-primary">Perbarui Produk</button>
        <a href="dashboard.php" class="btn btn-ghost">Batal</a>
      </div>
    </form>
  </div>
</div>
</div>
<div class="footer"><div class="container">© 2024 Toko Baju Online</div></div>
</body>
</html>
