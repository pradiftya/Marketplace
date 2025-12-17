<?php
session_start();
require_once "../../config/koneksi.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: ../../index.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("<div style='text-align:center;margin-top:50px;'><h3>❌ Order ID tidak ditemukan!</h3></div>");
}

$q = mysqli_query($conn, "SELECT status FROM payments WHERE order_id='$order_id' ORDER BY id DESC LIMIT 1");
$status = mysqli_num_rows($q) ? mysqli_fetch_assoc($q)['status'] : null;

if ($status === 'Diterima') {
    header("Location: pembayaran_diterima.php?order_id=$order_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Status Pembayaran</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(120deg,#007bff,#00b4d8);min-height:100vh;}
.card{border:none;border-radius:15px;background:white;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
</style>
<script>
setTimeout(()=>{location.reload();},5000);
</script>
</head>
<body>
<div class="container py-5">
  <div class="card p-4 mx-auto text-center" style="max-width:600px;">
    <h4 class="text-primary mb-3">🕓 Pembayaran Sedang Diproses</h4>
    <p class="lead">Status Pembayaran Anda: <strong class="text-warning">Menunggu Konfirmasi Admin</strong></p>
    <p>Halaman ini akan otomatis memperbarui jika admin sudah mengkonfirmasi pembayaran Anda.</p>
    <div class="spinner-border text-warning mt-3" role="status"></div>
    <a href="../../index.php" class="btn btn-secondary mt-4 w-100">← Kembali ke Beranda</a>
  </div>
</div>
</body>
</html>
