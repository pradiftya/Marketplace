<?php
    session_start();
    require_once "../config/koneksi.php";

    // Filter
    $filter = $_GET['filter'] ?? 'all';
    $filterQuery = "";
    $filterQueryPayroll = "";
    if ($filter === 'month') {
        $filterQuery = " AND MONTH(o.tanggal) = MONTH(CURDATE()) AND YEAR(o.tanggal) = YEAR(CURDATE()) ";
        $filterQueryPayroll = " AND MONTH(p.tanggal) = MONTH(CURDATE()) AND YEAR(p.tanggal) = YEAR(CURDATE())";
    } elseif ($filter === 'year') {
        $filterQuery = " AND YEAR(o.tanggal) = YEAR(CURDATE()) ";
        $filterQueryPayroll = " AND YEAR(p.tanggal) = YEAR(CURDATE())";
    }

    // =====================
    //  QUERY LAPORAN
    // =====================
    $sql_summary = "SELECT COALESCE(SUM(oi.harga * oi.qty), 0) AS total_omzet, COALESCE(SUM((oi.harga - p.modal) * oi.qty), 0) AS total_profit_kotor, (COALESCE(SUM((oi.harga - p.modal) * oi.qty), 0) - (SELECT COALESCE(SUM(py.total), 0) FROM payroll py WHERE 1=1 $filterQuery )) AS total_profit_bersih, COUNT(DISTINCT o.id) AS total_transaksi FROM orders o INNER JOIN order_items oi ON oi.order_id = o.id INNER JOIN products p ON p.id = oi.product_id WHERE o.status IN ('Selesai', 'Diproses Pengiriman') $filterQuery";
    $querySummaryPayroll = mysqli_query($conn, "SELECT SUM(o.total) AS total_payroll FROM payroll o WHERE 1=1 $filterQuery");
    $totalPayroll = mysqli_fetch_assoc($querySummaryPayroll)['total_payroll'] ?? 0;
    $res_summary = mysqli_query($conn, $sql_summary);
    $summary = mysqli_fetch_assoc($res_summary);

    $sql_detail = "SELECT o.*, u.nama AS customer FROM orders o LEFT JOIN users u ON o.customer_id = u.id WHERE (o.status = 'Selesai' OR o.status = 'Diproses Pengiriman') $filterQuery ORDER BY o.tanggal DESC";
    $res_detail = mysqli_query($conn, $sql_detail);

    $queryPayroll = "SELECT p.id, p.tanggal, u.nama AS nama_user, p.total FROM payroll p JOIN users u ON u.id = p.id_user WHERE 1=1 $filterQueryPayroll ORDER BY p.tanggal DESC";
    $resultPayroll = mysqli_query($conn, $queryPayroll);

    $queryFetchAdmin = "SELECT id, nama FROM users WHERE role = 'admin'";
    $fetchAdmin = mysqli_query($conn, $queryFetchAdmin);

    if (isset($_POST['postPayroll'])) {
        $id_user = $_POST['id_user'];
        $total   = $_POST['total'];
        $sql = "INSERT INTO payroll (id_user, total, tanggal) VALUES ('$id_user', '$total', NOW())";
        if (mysqli_query($conn, $sql)) {
            echo "<script>
                    alert('Data payroll berhasil disimpan');
                    window.location.href='laporan_keuangan.php';
                </script>";
        } else {
            echo "<script>
                    alert('Gagal menyimpan data');
                </script>";
        }
    }

    if (isset($_POST['updatePayroll'])) {
        $id    = $_POST['payroll_id'];
        $total = $_POST['total'];

        mysqli_query($conn, "
            UPDATE payroll
            SET total = '$total'
            WHERE id = '$id'
        ");

        echo "<script>
                alert('Data payroll berhasil diubah');
                window.location='laporan_keuangan.php';
            </script>";
    }

    if (isset($_POST['deletePayroll'])) {
        $id = $_POST['payroll_id'];
        mysqli_query($conn, "DELETE FROM payroll WHERE id = '$id'");
        echo "<script>
                alert('Data payroll berhasil dihapus');
                window.location='laporan_keuangan.php';
            </script>";
    }
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
    <div class="card p-4 my-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-center mb-0">📑 Laporan Keuangan</h4>
            <form method="GET" class="d-flex gap-2">
                <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $filter=='all'?'selected':'' ?>>Semua Waktu</option>
                    <option value="month" <?= $filter=='month'?'selected':'' ?>>Bulan Ini</option>
                    <option value="year" <?= $filter=='year'?'selected':'' ?>>Tahun Ini</option>
                </select>
            </form>
        </div>

        <!-- Ringkasan Keuangan -->
        <div class="row text-center">
            <div class="col-md-6 row text-center">
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Total Omzet</h6>
                            <h4>Rp <?= number_format($summary['total_omzet'],0,',','.'); ?></h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Biaya Operasional</h6>
                            <h4>Rp <?= number_format($totalPayroll,0,',','.'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 row text-center">
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Laba Kotor</h6>
                            <h4>Rp <?= number_format($summary['total_profit_kotor'],0,',','.'); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Laba Bersih</h6>
                            <h4>Rp <?= number_format($summary['total_profit_bersih'],0,',','.'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-4 my-3">
        <div class="row py-2">
            <div class="col-md-6">
                <h5 class="fw-bold text-secondary">Payroll</h5>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPayroll">
                    Tambah Payroll
                </button>  
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 20%;">Waktu</th>
                        <th style="width: 40%;">Karyawan</th>
                        <th style="width: 20%;">Total</th>
                        <th style="width: 20%;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (mysqli_num_rows($resultPayroll) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($resultPayroll)): ?>
                            <tr>
                                <td class="text-center">
                                    <?= date('d-m-Y H:i', strtotime($row['tanggal'])); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['nama_user']); ?>
                                </td>
                                <td class="text-center">
                                    Rp <?= number_format($row['total'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <form method="POST" class="row g-1">
                                        <input type="hidden" name="payroll_id" value="<?= $row['id']; ?>">

                                        <div class="col-6">
                                            <button type="submit" name="deletePayroll"
                                                    class="btn btn-danger w-100"
                                                    onclick="return confirm('Yakin hapus data payroll ini?')">
                                                Hapus
                                            </button>
                                        </div>

                                        <div class="col-6">
                                            <button type="button"
                                                    class="btn btn-primary w-100 btn-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditPayroll"
                                                    data-id="<?= $row['id']; ?>"
                                                    data-total="<?= $row['total']; ?>">
                                                Ubah
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Belum ada data payroll
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (mysqli_num_rows($resultPayroll) > 0): ?>
                <a href="?export=excel&filter=<?= $filter ?>" class="btn btn-info text-white w-100 p-2">
                    ⬇️ Unduh Laporan
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card p-4 my-3">
        <h5 class="fw-bold text-secondary mb-3">Riwayat Transaksi</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 20%;">Tanggal</th>
                        <th style="width: 40%;">Customer</th>
                        <th style="width: 20%;">Total</th>
                        <th style="width: 20%;">Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res_detail)) : ?>
                        <tr>
                            <td class="text-center"><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['customer'] ?? '-') ?></td>
                            <td class="text-center">Rp <?= number_format($row['total'],0,',','.') ?></td>
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

    <div class="modal fade" id="modalPayroll" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Payroll</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Admin</label>
                            <select name="id_user" class="form-select" required>
                                <option value="">-- Pilih Admin --</option>
                                <?php while ($row = mysqli_fetch_assoc($fetchAdmin)) : ?>
                                    <option value="<?= $row['id']; ?>">
                                        <?= $row['nama']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total</label>
                            <input type="number" name="total" step="0.01" class="form-control" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="postPayroll" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditPayroll" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Payroll</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="payroll_id" id="edit_payroll_id">
                        <div class="mb-3">
                            <label>Total</label>
                            <input type="number" name="total" id="edit_total" class="form-control" step="0.01" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="updatePayroll" class="btn btn-primary">
                            Simpan Perubahan
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('edit_payroll_id').value = this.dataset.id;
                document.getElementById('edit_total').value = this.dataset.total;
            });
        });
    });
</script>

</html>
