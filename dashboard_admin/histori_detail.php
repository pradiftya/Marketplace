<?php
session_start();
require_once "../config/koneksi.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location='../index.php';</script>";
    exit();
}

$id = $_GET['id'] ?? 0;

$order = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT o.*, u.nama 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id
    WHERE o.id = $id
"));

$items = mysqli_query($conn, "
    SELECT oi.*, p.nama 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $id
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            #containerButton {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card p-3">
        <h4>Detail Order #<?= $order['id'] ?></h4>
        <p><b>Customer:</b> <?= $order['nama'] ?></p>
        <p><b>Tanggal:</b> <?= $order['tanggal'] ?></p>
        <p><b>Status:</b> <?= $order['status'] ?></p>

        <hr>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($i = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= $i['nama'] ?></td>
                    <td><?= $i['qty'] ?></td>
                    <td>Rp <?= number_format($i['harga'],0,',','.') ?></td>
                    <td>Rp <?= number_format($i['harga'] * $i['qty'],0,',','.') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="row g-2 mt-1" id="containerButton">
            <div class="col-md-6">
                <a href="javascript:history.back()" class="btn btn-primary w-100 p-2">
                    ⬅️ Kembali
                </a>
            </div>
            <div class="col-md-6">
                <button onclick="window.print()" class="btn btn-info text-white w-100 p-2">
                    ⬇️ Unduh Resi
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
