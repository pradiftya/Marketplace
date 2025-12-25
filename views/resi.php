<?php
session_start();
require_once "../config/koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("<h3>❌ Order ID tidak ditemukan!</h3>");
}

$q = mysqli_query($conn, "
    SELECT o.*, u.nama 
    FROM orders o 
    JOIN users u ON u.id = o.customer_id 
    WHERE o.id='$order_id'
");

$order = mysqli_fetch_assoc($q);

if ($order['status'] == 'Dibatalkan') {
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$order_id'");
    mysqli_query($conn, "DELETE FROM orders WHERE id='$order_id'");
    echo "<script>alert('Pesanan ini sudah dibatalkan dan resi dihapus.'); window.location='../index.php';</script>";
    exit();
}

$q_items = mysqli_query($conn, "
    SELECT oi.*, p.nama 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id='$order_id'
");

if (isset($_POST['konfirmasi'])) {
    mysqli_query($conn, "
        UPDATE orders 
        SET status='Selesai' 
        WHERE id='$order_id'
    ");
    header("Location: resi.php?order_id=$order_id&done=1");
    exit();
}

$badge = "bg-secondary";

switch ($order['status']) {
    case "Belum Bayar":
        $badge = "bg-warning text-dark"; break;
    case "Sedang Diproses":
        $badge = "bg-primary"; break;
    case "Pengemasan":
        $badge = "bg-info text-dark"; break;
    case "Pengiriman":
        $badge = "bg-dark"; break;
    case "Selesai":
        $badge = "bg-success"; break;
    case "Dibatalkan":
        $badge = "bg-danger"; break;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Resi Pembelian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            #containerInfo,
            #containerButton {
                display: none !important;
            }
        }
        body{
            background:#f5f5f5;
        }
        .card{
            border-radius:12px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4 shadow">
            <h3 class="text-primary">📦 RESI PEMBELIAN</h3>
            <hr>
            <?php if (isset($_GET['done'])): ?>
                <div class="alert alert-success">✔ Barang berhasil dikonfirmasi telah sampai!</div>
            <?php endif; ?>
            <p><b>Nama Pembeli:</b> <?= htmlspecialchars($order['nama']); ?></p>
            <p><b>Tanggal Pesanan:</b> <?= $order['tanggal']; ?></p>
            <p><b>Status Pesanan:</b>
                <span class="badge <?= $badge ?>"><?= $order['status']; ?></span>
            </p>
            <h5 class="mt-4">🛍️ Detail Produk</h5>
            <table class="table table-bordered">
                <tr>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                </tr>
                <?php while($i = mysqli_fetch_assoc($q_items)): ?>
                <tr>
                    <td><?= $i['nama']; ?></td>
                    <td><?= $i['qty']; ?></td>
                    <td>Rp <?= number_format($i['harga'],0,',','.'); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <div id="containerInfo">
                <?php if ($order['status'] != 'Selesai' && $order['status'] != 'Belum Bayar' && $order['status'] != 'Dibatalkan'): ?>
                    <form method="post">
                        <button type="submit" name="konfirmasi" class="btn btn-success w-100 mt-3">
                            ☑️ Konfirmasi Barang Telah Sampai
                        </button>
                    </form>

                <?php elseif ($order['status'] == 'Belum Bayar'): ?>
                    <div class="alert alert-warning mt-3 text-center">
                        ⚠ Anda belum melakukan pembayaran.
                    </div>

                <?php elseif ($order['status'] == 'Dibatalkan'): ?>
                    <div class="alert alert-danger mt-3 text-center">
                        ❌ Pesanan ini sudah dibatalkan.
                    </div>

                <?php else: ?>
                    <div class="alert alert-success mt-3 text-center">
                        ☑️ Pesanan selesai. Terima kasih sudah berbelanja!
                    </div>
                <?php endif; ?>
            </div>
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
