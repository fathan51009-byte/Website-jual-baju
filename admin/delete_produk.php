<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include_once '../includes/koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$product_id = (int)$_GET['id'];

// Ambil nama gambar dulu agar bisa dihapus dari folder
$stmt_sel = mysqli_prepare($conn, "SELECT gambar FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt_sel, "i", $product_id);
mysqli_stmt_execute($stmt_sel);
mysqli_stmt_bind_result($stmt_sel, $gambar);
mysqli_stmt_fetch($stmt_sel);
mysqli_stmt_close($stmt_sel);

// Hapus produk
$stmt_del = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt_del, "i", $product_id);

if (mysqli_stmt_execute($stmt_del)) {
    // Hapus file gambar dari server jika ada
    if ($gambar && file_exists("../uploads/" . $gambar)) {
        unlink("../uploads/" . $gambar);
    }
    header("Location: dashboard.php?status=deleted");
} else {
    echo "Gagal menghapus produk: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt_del);
mysqli_close($conn);
exit();
?>
