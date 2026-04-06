<nav class="navbar">
  <div class="container navbar-inner">
    <a href="dashboard.php" class="navbar-brand">
      <span></span> Toko Baju — Admin
    </a>
    <ul class="navbar-links">
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="tambah_produk.php">+ Produk</a></li>
    </ul>
    <div class="navbar-user">
      👤 <?= htmlspecialchars($_SESSION['username']) ?> &nbsp;|&nbsp;
      <a href="../logout.php">Keluar</a>
    </div>
  </div>
</nav>
