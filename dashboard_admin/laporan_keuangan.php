<?php
    session_start();
    require_once "../config/koneksi.php";

    // Filter
    $filter = $_GET['filter'] ?? 'all';
    $filterQuery = "";
    if ($filter === 'month') {
        $filterQuery = " AND MONTH(o.tanggal) = MONTH(CURDATE()) AND YEAR(o.tanggal) = YEAR(CURDATE()) ";
    } elseif ($filter === 'year') {
        $filterQuery = " AND YEAR(o.tanggal) = YEAR(CURDATE()) ";
    }

    // =====================
    //  QUERY LAPORAN
    // =====================
    $sql_summary = "SELECT COALESCE(SUM(oi.harga * oi.qty), 0) AS total_omzet, COALESCE(SUM((oi.harga - p.modal) * oi.qty), 0) AS total_profit, COUNT(DISTINCT o.id) AS total_transaksi FROM orders o INNER JOIN order_items oi ON oi.order_id = o.id INNER JOIN products p ON p.id = oi.product_id WHERE o.status IN ('Selesai', 'Diproses Pengiriman') $filterQuery";
    $res_summary = mysqli_query($conn, $sql_summary);
    $summary = mysqli_fetch_assoc($res_summary);
    $sql_detail = "SELECT o.*, u.nama AS customer FROM orders o LEFT JOIN users u ON o.customer_id = u.id WHERE (o.status = 'Selesai' OR o.status = 'Diproses Pengiriman') $filterQuery ORDER BY o.tanggal DESC";
    $res_detail = mysqli_query($conn, $sql_detail);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f2f7ff;
            min-height: 100vh;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .navbar-custom {
            background: linear-gradient(120deg, #007bff, #00b4d8);
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<div class="container py-5">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-primary mb-0">📑 Laporan Keuangan</h4>
            <form method="GET" class="d-flex gap-2">
                <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $filter=='all'?'selected':'' ?>>Semua Waktu</option>
                    <option value="month" <?= $filter=='month'?'selected':'' ?>>Bulan Ini</option>
                    <option value="year" <?= $filter=='year'?'selected':'' ?>>Tahun Ini</option>
                </select>
            </form>
        </div>

        <!-- Ringkasan Keuangan -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Laba Kotor</h6>
                        <h4>Rp <?= number_format($summary['total_omzet'],0,',','.'); ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Laba Bersih</h6>
                        <h4>Rp <?= number_format($summary['total_profit'],0,',','.'); ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Total Transaksi</h6>
                        <h4><?= number_format($summary['total_transaksi'],0,',','.'); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold text-secondary mb-3">Detail Transaksi</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res_detail)) : ?>
                        <tr>
                            <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['customer'] ?? '-') ?></td>
                            <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
                            <td class="text-center"><?= $row['status'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <a href="?export=excel&filter=<?= $filter ?>" class="btn btn-info text-white w-100 p-2">
            ⬇️ Unduh Laporan
        </a>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
