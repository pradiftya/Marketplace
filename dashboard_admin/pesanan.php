<?php
session_start();
require_once "../config/koneksi.php";

// Pastikan hanya admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// ✅ Hitung jumlah per status
$status_counts = [];
$statuses = ['Belum Bayar', 'Sedang Diproses', 'Pengemasan', 'Pengiriman', 'Selesai'];
foreach ($statuses as $s) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE status='$s'");
    $row = mysqli_fetch_assoc($res);
    $status_counts[$s] = $row['total'] ?? 0;
}

// ✅ Update status pesanan manual
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status_baru = $_POST['status_baru'];
    mysqli_query($conn, "UPDATE orders SET status='$status_baru' WHERE id='$order_id'");
    echo "<script>alert('✅ Status pesanan diperbarui menjadi $status_baru'); window.location='pesanan.php';</script>";
    exit();
}

// ✅ Konfirmasi pembayaran
if (isset($_GET['konfirmasi'])) {
    $order_id = $_GET['konfirmasi'];

    // Generate nomor resi otomatis
    $resi = "RS" . date("Ymd") . strtoupper(substr(md5(time()), 0, 6));

    // Vendor default (bisa diganti sesuai keinginan)
    $vendor = "JNE";

    // Update payment + tambah resi + vendor
mysqli_query($conn, "
    UPDATE payments 
    SET status='Diterima', 
        vendor_pengirim='$vendor',
        resi='$resi'
    WHERE order_id='$order_id'
");

// Pastikan order otomatis lanjut ke proses
mysqli_query($conn, "
    UPDATE orders 
    SET status='Sedang Diproses'
    WHERE id='$order_id'
");


    // Ubah status order
    mysqli_query($conn, "
        UPDATE orders 
        SET status='Sedang Diproses'
        WHERE id='$order_id'
    ");

    echo "<script>
            alert('✅ Pembayaran dikonfirmasi!\\nNomor Resi: $resi');
            window.location='pesanan.php';
          </script>";
    exit();
}


// ✅ Ambil semua pesanan
$q = mysqli_query($conn, "
    SELECT o.*, u.nama AS customer, p.metode, p.status AS payment_status, 
           p.vendor_pengirim, p.resi, p.bukti
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    LEFT JOIN payments p ON o.id = p.order_id
    ORDER BY o.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin - Data Pesanan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(120deg, #007bff, #00b4d8);
        min-height: 100vh;
    }
    .card {
        border: none;
        border-radius: 15px;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        animation: fadeIn 0.7s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .table img {
        border-radius: 10px;
        transition: 0.3s;
    }
    .table img:hover {
        transform: scale(1.8);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        z-index: 1000;
    }
    .status-info {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .status-info span {
        padding: 10px 18px;
        border-radius: 25px;
        font-weight: 600;
        color: #fff;
        user-select: none;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .status-belumbayar { background: #0dcaf0; color: #000; }
    .status-proses { background: #0dcaf0; color: #000; }
    .status-pengemasan { background: #0dcaf0; color: #000; }
    .status-pengiriman { background: #0dcaf0; color: #000; }
    .status-selesai { background: #0dcaf0; color: #000; }
</style>
</head>
<body>

<div class="container py-5">
  <div class="card p-4">
    <h4 class="text-primary text-center mb-4">📦 Daftar Pesanan Pelanggan</h4>

    <!-- 🔔 Status Info + Jumlah -->
    <div class="status-info">
        <span class="status-belumbayar">Belum Bayar: <?= $status_counts['Belum Bayar'] ?? 0; ?></span>
        <span class="status-proses">Sedang Diproses: <?= $status_counts['Sedang Diproses'] ?? 0; ?></span>
        <span class="status-pengemasan">Pengemasan: <?= $status_counts['Pengemasan'] ?? 0; ?></span>
        <span class="status-pengiriman">Pengiriman: <?= $status_counts['Pengiriman'] ?? 0; ?></span>
        <span class="status-selesai">Selesai: <?= $status_counts['Selesai'] ?? 0; ?></span>
      </div>

    <table class="table table-bordered table-hover align-middle">
      <thead class="table-primary text-center">
        <tr>
          <th>#</th>
          <th>Pelanggan</th>
          <th>Total</th>
          <th>Status Pesanan</th>
          <th>Metode Pembayaran</th>
          <th>Status Pembayaran</th>
          <th>Vendor</th>
          <th>Resi</th>
          <th>Bukti Pembayaran</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; while ($row = mysqli_fetch_assoc($q)): ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= htmlspecialchars($row['customer']); ?></td>
          <td>Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>

          <!-- 🔄 Status pesanan editable -->
          <td>
            <form method="POST" class="d-flex align-items-center justify-content-center gap-2">
                <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                <select name="status_baru" class="form-select form-select-sm" style="width:150px;">
                   <?php if ($row['payment_status'] != 'Diterima'): ?>
    <option value="Belum Bayar" <?= $row['status']=='Belum Bayar'?'selected':''; ?>>Belum Bayar</option>
<?php endif; ?>

                <option value="Sedang Diproses" <?= $row['status']=='Sedang Diproses'?'selected':''; ?>>Sedang Diproses</option>
                    <option value="Pengemasan" <?= $row['status']=='Pengemasan'?'selected':''; ?>>Pengemasan</option>
                    <option value="Pengiriman" <?= $row['status']=='Pengiriman'?'selected':''; ?>>Pengiriman</option>
                    <option value="Selesai" <?= $row['status']=='Selesai'?'selected':''; ?>>Selesai</option>
                </select>
                <button type="submit" name="update_status" class="btn btn-outline-primary btn-sm">✔</button>
            </form>
          </td>

          <td><?= htmlspecialchars($row['metode'] ?? '-'); ?></td>
          <td class="text-center">
            <span class="badge 
              <?= $row['payment_status']=='Diterima' ? 'bg-success' : 
                  ($row['payment_status']=='Menunggu Konfirmasi Admin' ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
              <?= htmlspecialchars($row['payment_status'] ?? 'Belum Bayar'); ?>
            </span>
          </td>
          <td><?= htmlspecialchars($row['vendor_pengirim'] ?? '-'); ?></td>
          <td><?= htmlspecialchars($row['resi'] ?? '-'); ?></td>

         <td class="text-center">
    <?php if (!empty($row['bukti'])): ?>
        <a href="../<?= htmlspecialchars($row['bukti']); ?>" target="_blank">
            <img src="../<?= htmlspecialchars($row['bukti']); ?>" 
                 width="90" height="90" style="object-fit:cover; border-radius:10px;">
        </a>
    <?php else: ?>
        <span class="text-muted">Belum ada</span>
    <?php endif; ?>
</td>


          <td class="text-center">
            <?php if ($row['payment_status'] == 'Menunggu Konfirmasi Admin'): ?>
              <a href="?konfirmasi=<?= $row['id']; ?>" class="btn btn-success btn-sm">Konfirmasi</a>
            <?php elseif ($row['payment_status'] == 'Diterima'): ?>
              <span class="text-muted">✔ Diterima</span>
            <?php else: ?>
              <span class="text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

   <div class="text-end mt-3">
  <a href="admin.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
</div>

  </div>
</div>

</body>
</html>
