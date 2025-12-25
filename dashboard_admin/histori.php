<?php
session_start();
require_once "../config/koneksi.php";

// Ambil data orders + customer
$orders = mysqli_query($conn, "
    SELECT o.*, u.nama 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id
    ORDER BY o.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Histori Pembelian</title>

<style>
    body {
        background: linear-gradient(120deg, #007bff, #00b4d8);
        min-height: 100vh;
    }
    .card { border-radius: 12px; }
    .table thead { background: #007bff; color: white; }
</style>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="container mt-4">
    <h3 class="text-white mb-4">🧾 Histori Pembelian</h3>

    <div class="card p-3">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID Order</th>
                    <th>Customer</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php while($o = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= $o['nama'] ?></td>
                    <td><?= $o['tanggal'] ?></td>
                    <td>Rp <?= number_format($o['total'],0,',','.') ?></td>
                    <td><?= $o['status'] ?></td>
                    <td>
                        <a href="histori_detail.php?id=<?= $o['id'] ?>" 
                           class="btn btn-primary btn-sm">Lihat</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
