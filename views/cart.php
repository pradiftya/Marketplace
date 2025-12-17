<?php
session_start();
require_once "../config/koneksi.php";

// Cegah akses langsung jika belum login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='../index.php';</script>";
    exit();
}

// === HAPUS PRODUK DARI KERANJANG ===
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    echo "<script>alert('Produk dihapus dari keranjang!'); window.location='cart.php';</script>";
    exit();
}

// === CHECKOUT ===
if (isset($_POST['checkout'])) {

    if (empty($_SESSION['cart'])) {
        echo "<script>alert('Keranjang masih kosong!'); window.location='cart.php';</script>";
        exit();
    }

    // CEK STOK SEMUA PRODUK TERLEBIH DAHULU
    foreach ($_SESSION['cart'] as $id_products => $item) {
        $q = mysqli_query($conn, "SELECT stok FROM products WHERE id='$id_products'");
        $p = mysqli_fetch_assoc($q);

        if ($p['stok'] <= 0) {
            echo "<script>alert('Produk \"{$item['nama']}\" sudah habis! Hapus dari keranjang.'); window.location='cart.php';</script>";
            exit();
        }

        if ($item['qty'] > $p['stok']) {
            echo "<script>alert('Stok produk \"{$item['nama']}\" tidak mencukupi! Maks: {$p['stok']}'); window.location='cart.php';</script>";
            exit();
        }
    }

    // ===== JIKA SEMUA STOK CUKUP, LANJUTKAN CHECKOUT =====
    $customer_id = $_SESSION['user']['id'];
    $tanggal = date("Y-m-d H:i:s");
    $total = 0;

    foreach ($_SESSION['cart'] as $item) {
        $total += $item['harga'] * $item['qty'];
    }

    mysqli_query($conn, "INSERT INTO orders (customer_id, tanggal, status, total) 
                         VALUES ('$customer_id', '$tanggal', 'Menunggu Pembayaran', '$total')");
    $order_id = mysqli_insert_id($conn);

    foreach ($_SESSION['cart'] as $id_produk => $item) {
        $qty = $item['qty'];
        $harga = $item['harga'];

        // Simpan ke tabel order_items
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, harga)
                             VALUES ('$order_id', '$id_produk', '$qty', '$harga')");

        // Kurangi stok di database
        $q2 = mysqli_query($conn, "SELECT stok FROM products WHERE id='$id_produk'");
        $p2 = mysqli_fetch_assoc($q2);

        $stok_baru = $p2['stok'] - $qty;
        if ($stok_baru < 0) $stok_baru = 0;

        mysqli_query($conn, "UPDATE products SET stok='$stok_baru' WHERE id='$id_produk'");
    }

    unset($_SESSION['cart']);
    header("Location: ../payment.php?order_id=$order_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Keranjang Belanja - Pangsisssst Marketplace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
body {
  background: linear-gradient(120deg, #007bff, #00b4d8);
  min-height: 100vh;
  color: #212529;
}
.navbar-custom {
  background: linear-gradient(120deg, #007bff, #00b4d8);
}
.btn-custom {
  background-color: #00b4d8;
  color: white;
  border: none;
  transition: 0.3s;
}
.btn-custom:hover {
  background-color: #007bff;
}
.card {
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
footer {
  background: linear-gradient(120deg, #007bff, #00b4d8);
  color: white;
  text-align: center;
  padding: 20px;
  margin-top: 40px;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../index.php">🥨 Pangsisssst</a>
    <a href="../index.php" class="btn btn-light btn-sm">← Kembali</a>
  </div>
</nav>

<div class="container mt-5">
  <div class="card p-4">
    <h3 class="text-center text-primary mb-4">🛒 Keranjang Belanja Anda</h3>

    <?php if (!empty($_SESSION['cart'])): ?>
    <form method="POST">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-primary">
          <tr>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $total = 0;
          foreach ($_SESSION['cart'] as $id => $item): 
            $subtotal = $item['harga'] * $item['qty'];
            $total += $subtotal;
          ?>
          <tr>
            <td><?= htmlspecialchars($item['nama']); ?></td>
            <td><?= $item['qty']; ?></td>
            <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
            <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
            <td>
              <a href="?remove=<?= $id; ?>" class="btn btn-danger btn-sm">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <tr class="fw-bold table-light">
            <td colspan="3">Total</td>
            <td colspan="2">Rp <?= number_format($total, 0, ',', '.'); ?></td>
          </tr>
        </tbody>
      </table>
      <button type="submit" name="checkout" class="btn btn-success w-100 mt-3">
        <i class="bi bi-cash-stack"></i> Checkout Sekarang
      </button>
    </form>
    <?php else: ?>
      <div class="text-center py-5">
        <h5 class="text-muted">Keranjang Anda masih kosong 😢</h5>
        <a href="../index.php" class="btn btn-custom mt-3">Lihat Produk</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer>
  <div class="container">
    <h6 class="fw-bold mb-2">Hubungi Kami</h6>
    <div class="d-flex justify-content-center gap-3 mb-3">
      <a href="https://wa.me/6281234567890" target="_blank" class="text-white text-decoration-none"><i class="bi bi-whatsapp"></i> WhatsApp</a>
      <a href="https://instagram.com/nehan_ramadhan" target="_blank" class="text-white text-decoration-none"><i class="bi bi-instagram"></i> Instagram</a>
    </div>
    <p class="small mb-0">© <?= date('Y'); ?> Pangsisssst Marketplace</p>
  </div>
</footer>

</body>
</html>
