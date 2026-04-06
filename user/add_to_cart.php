<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id  = (int)$_POST['product_id'];
    $nama_produk = htmlspecialchars($_POST['nama_produk']);
    $harga       = (float)$_POST['harga'];
    $quantity    = max(1, (int)$_POST['quantity']);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Jika produk sudah ada di keranjang, tambahkan quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id'  => $product_id,
            'nama_produk' => $nama_produk,
            'harga'       => $harga,
            'quantity'    => $quantity,
        ];
    }
}

header("Location: cart.php");
exit();
?>
