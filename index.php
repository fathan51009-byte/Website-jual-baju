<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') { header("Location: admin/dashboard.php"); }
    else { header("Location: user/dashboard.php"); }
    exit();
}
include_once 'includes/koneksi.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $error = "Username dan password tidak boleh kosong.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, password, role FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $hashed_password, $role);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($id && password_verify($password, $hashed_password)) {
            $_SESSION['user_id']  = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role']     = $role;
            header($role === 'admin' ? "Location: admin/dashboard.php" : "Location: user/dashboard.php");
            exit();
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Toko Baju Online</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-icon"></div>
      <span class="auth-logo-text">Toko Baju</span>
    </div>
    <h2>Selamat Datang</h2>
    <p class="auth-subtitle">Masuk ke akun Anda untuk melanjutkan</p>
    <?php if ($error): ?>
      <div class="alert alert-danger">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="index.php" method="POST">
      <div class="form-group">
        <label>Username</label>
        <input class="form-control" type="text" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Masukkan username" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input class="form-control" type="password" name="password"
               placeholder="Masukkan password" required>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-full">Masuk</button>
      </div>
    </form>
    <p class="text-sm text-muted" style="text-align:center; margin-top:24px;">
      Belum punya akun? <a href="register.php">Daftar sekarang</a>
    </p>
  </div>
</div>
</body>
</html>
