<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit(); }
include_once '../includes/koneksi.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $harga = $_POST['harga']; $stok = $_POST['stok']; $deskripsi = trim($_POST['deskripsi']);
    if (empty($nama_produk) || empty($harga) || empty($stok)) { $error = "Nama produk, harga, dan stok wajib diisi."; }
    elseif (!is_numeric($harga) || $harga < 0) { $error = "Harga harus berupa angka positif."; }
    elseif (!is_numeric($stok) || $stok < 0) { $error = "Stok harus berupa angka positif."; }
    else {
        $gambar_nama = '';
        if (!empty($_FILES['gambar']['name'])) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($_FILES['gambar']['type'], $allowed)) { $error = "Format gambar tidak didukung."; }
            elseif ($_FILES['gambar']['size'] > 2*1024*1024) { $error = "Ukuran gambar maks 2MB."; }
            else {
                $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $gambar_nama = uniqid('prod_').'.'.$ext;
                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], "../uploads/".$gambar_nama)) {
                    $error = "Gagal mengupload gambar."; $gambar_nama = '';
                }
            }
        }
        if (empty($error)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (nama_produk, harga, stok, deskripsi, gambar) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "sdiis", $nama_produk, $harga, $stok, $deskripsi, $gambar_nama);
            if (mysqli_stmt_execute($stmt)) { header("Location: dashboard.php?status=added"); exit(); }
            else { $error = "Gagal menyimpan produk."; }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Produk — Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">
<?php include_once '../includes/navbar_admin.php'; ?>
<div class="main-content">
<div class="container" style="max-width:640px;">

  <div class="page-header">
    <a href="dashboard.php" class="btn btn-ghost btn-sm" style="margin-bottom:12px;">← Kembali</a>
    <h1>Tambah Produk</h1>
    <p>Isi detail produk baru yang akan ditampilkan di katalog</p>
  </div>

  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-danger">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="tambah_produk.php" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>Nama Produk *</label>
        <input class="form-control" type="text" name="nama_produk"
               value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>"
               placeholder="cth: Kemeja Oxford Putih" required>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="form-group">
          <label>Harga (Rp) *</label>
          <input class="form-control" type="number" name="harga" min="0"
                 value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>"
                 placeholder="150000" required>
        </div>
        <div class="form-group">
          <label>Stok *</label>
          <input class="form-control" type="number" name="stok" min="0"
                 value="<?= htmlspecialchars($_POST['stok'] ?? '') ?>"
                 placeholder="50" required>
        </div>
      </div>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea class="form-control" name="deskripsi" rows="3"
                  placeholder="Deskripsi singkat produk..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Gambar Produk <span class="text-muted text-sm">(JPG/PNG, maks 2MB)</span></label>
        <input class="form-control" type="file" name="gambar" accept="image/*">
      </div>
      <div style="display:flex; gap:12px; margin-top:8px;">
        <button type="submit" class="btn btn-primary">Simpan Produk</button>
        <a href="dashboard.php" class="btn btn-ghost">Batal</a>
      </div>
    </form>
  </div>

</div>
</div>
<div class="footer"><div class="container">© 2024 Toko Baju Online</div></div>
</body>
</html>
