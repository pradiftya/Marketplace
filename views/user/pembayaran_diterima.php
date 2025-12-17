<?php
session_start();
require_once "../../config/koneksi.php";

// Hanya customer yang boleh akses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: ../../index.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("<div style='text-align:center;margin-top:50px;'><h3>❌ Order ID tidak ditemukan!</h3></div>");
}

// 🔎 Ambil data pesanan + resi
$q = mysqli_query($conn, "
    SELECT o.id, o.total, o.status, 
           p.resi, p.vendor_pengirim, p.metode
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.id = '$order_id'
");

$order = mysqli_fetch_assoc($q);

if (!$order) {
    die("<div style='text-align:center;margin-top:50px;'><h3>❌ Data pesanan tidak ditemukan!</h3></div>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pembayaran Diterima</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(120deg,#007bff,#00b4d8);min-height:100vh;}
.card{border:none;border-radius:15px;background:white;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
.resi-box{
    background:#f8f9fa;
    border:2px dashed #0d6efd;
    padding:15px;
    border-radius:10px;
    font-size:18px;
}
</style>
</head>
<body>
<div class="container py-5">
  <div class="card p-4 mx-auto text-center" style="max-width:650px;">
    <h3 class="text-success mb-3">✅ Pembayaran sudah diterima!</h3>
    <p class="lead">Pesanan Anda sedang diproses 💙</p>

    <hr>

    <h5 class="text-primary">📦 Informasi Pengiriman</h5>

    <?php if (!empty($order['resi'])): ?>
        <div class="resi-box my-3">
            <strong>Vendor:</strong> <?= htmlspecialchars($order['vendor_pengirim']); ?><br>
            <strong>Nomor Resi:</strong> <?= htmlspecialchars($order['resi']); ?>
        </div>
    <?php else: ?>
        <div class="resi-box my-3 text-danger">
            <strong>Resi belum tersedia.</strong><br>
            Admin akan menginput nomor resi setelah barang dikirim.
        </div>
    <?php endif; ?>

    <hr>

    <p><strong>Total Pembayaran:</strong> Rp <?= number_format($order['total'],0,',','.'); ?></p>

    <a href="../../index.php" class="btn btn-primary mt-3 w-100">← Kembali ke Beranda</a>
  </div>
</div>
</body>
</html>
