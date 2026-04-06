<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
include_once 'includes/koneksi.php';
$error = ''; $success = ''; $username_val = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $username_val = $username;
    if (empty($username)) { $error = "Username tidak boleh kosong."; }
    elseif (strlen($username) < 3) { $error = "Username minimal 3 karakter."; }
    elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) { $error = "Username hanya boleh huruf, angka, dan underscore."; }
    elseif (empty($password)) { $error = "Password tidak boleh kosong."; }
    elseif (strlen($password) < 6) { $error = "Password minimal 6 karakter."; }
    elseif ($password !== $confirm_password) { $error = "Konfirmasi password tidak cocok."; }
    else {
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Username sudah terdaftar.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Registrasi berhasil! Mengarahkan ke halaman login...";
                $username_val = '';
                header("Refresh: 3; URL=index.php");
            } else { $error = "Gagal registrasi. Coba lagi."; }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi — Toko Baju Online</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-icon"></div>
      <span class="auth-logo-text">Toko Baju</span>
    </div>
    <h2>Buat Akun Baru</h2>
    <p class="auth-subtitle">Daftar dan mulai belanja sekarang</p>
    <?php if ($error): ?>
      <div class="alert alert-danger">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form action="register.php" method="POST">
      <div class="form-group">
        <label>Username</label>
        <input class="form-control" type="text" name="username"
               value="<?= htmlspecialchars($username_val) ?>"
               placeholder="Minimal 3 karakter" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input class="form-control" type="password" name="password"
               placeholder="Minimal 6 karakter" required>
      </div>
      <div class="form-group">
        <label>Konfirmasi Password</label>
        <input class="form-control" type="password" name="confirm_password"
               placeholder="Ulangi password" required>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-full">Daftar</button>
      </div>
    </form>
    <p class="text-sm text-muted" style="text-align:center; margin-top:24px;">
      Sudah punya akun? <a href="index.php">Masuk di sini</a>
    </p>
  </div>
</div>
</body>
</html>
