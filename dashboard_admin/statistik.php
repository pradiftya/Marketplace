<?php
session_start();
require_once "../config/koneksi.php";

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Tentukan filter waktu
$filter = $_GET['filter'] ?? 'all';

// ====== BUAT FILTER WAKTU UNTUK QUERY ======
$filterQuery = "";
if ($filter === 'month') {
    $filterQuery = " AND MONTH(o.tanggal) = MONTH(CURDATE()) AND YEAR(o.tanggal) = YEAR(CURDATE()) ";
} elseif ($filter === 'year') {
    $filterQuery = " AND YEAR(o.tanggal) = YEAR(CURDATE()) ";
}

// ====== QUERY STATISTIK PRODUK TERJUAL ======
$sql = "SELECT p.nama AS nama_produk, 
               COALESCE(SUM(oi.qty), 0) AS total_terjual
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE (o.status = 'Selesai' OR o.status = 'Diproses Pengiriman')
        $filterQuery
        GROUP BY p.id
        ORDER BY total_terjual DESC";

$result = mysqli_query($conn, $sql);

$produk = [];
$jumlah = [];
while ($row = mysqli_fetch_assoc($result)) {
    $produk[] = $row['nama_produk'];
    $jumlah[] = (int)$row['total_terjual'];
}

// ====== QUERY TOTAL OMZET & TOTAL TRANSAKSI ======
$sql2 = "SELECT 
            COALESCE(SUM(o.total), 0) AS total_omzet,
            COUNT(o.id) AS total_transaksi
         FROM orders o
         WHERE (o.status = 'Selesai' OR o.status = 'Diproses Pengiriman')
         $filterQuery";

$res2 = mysqli_query($conn, $sql2);
$data2 = mysqli_fetch_assoc($res2);

$total_omzet      = $data2['total_omzet'];
$total_transaksi  = $data2['total_transaksi'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Statistik Penjualan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: linear-gradient(120deg, #007bff, #00b4d8);
            min-height: 100vh;
            color: #212529;
        }
        .card {
            border-radius: 15px;
            border: none;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .navbar-custom {
            background: linear-gradient(120deg, #007bff, #00b4d8);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../admin.php">Pangsisssst Admin Panel</a>
    <div class="d-flex align-items-center">
      <span class="navbar-text text-white me-3">
        Halo, <?= htmlspecialchars($_SESSION['user']['nama']); ?>
      </span>
      <a href="../index.php?action=logout" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5">
    <div class="card p-4 mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-primary mb-0">📊 Statistik Penjualan Produk</h4>

            <!-- Filter Waktu -->
            <form method="GET" class="d-flex gap-2">
                <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Semua Waktu</option>
                    <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>Bulan Ini</option>
                    <option value="year" <?= $filter === 'year' ? 'selected' : '' ?>>Tahun Ini</option>
                </select>
            </form>
        </div>

        <!-- Ringkasan -->
        <div class="row text-center mb-4">
            <div class="col-md-6">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-1">Total Omzet</h6>
                        <h4>Rp <?= number_format($total_omzet, 0, ',', '.'); ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-1">Total Transaksi Selesai</h6>
                        <h4><?= number_format($total_transaksi, 0, ',', '.'); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Penjualan -->
        <canvas id="chartPenjualan" height="120"></canvas>

        <div class="text-center mt-4">
            <a href="admin.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
        </div>

    </div>
</div>

<!-- CHART JS -->
<script>
const ctx = document.getElementById('chartPenjualan').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($produk) ?>,
        datasets: [{
            label: 'Jumlah Produk Terjual',
            data: <?= json_encode($jumlah) ?>,
            backgroundColor: 'rgba(0, 123, 255, 0.7)',
            borderColor: 'rgba(0, 123, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Statistik Penjualan Produk <?= 
                    $filter === "month" ? "Bulan Ini" :
                    ($filter === "year" ? "Tahun Ini" : "(Semua Waktu)") 
                ?>'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

</body>
</html>
