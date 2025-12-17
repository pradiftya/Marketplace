<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "./config/koneksi.php";

// Pastikan hanya customer
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("<div style='text-align:center;margin-top:50px;'>
            <h3>❌ Order ID tidak ditemukan!</h3>
            <a href='index.php' class='btn btn-primary mt-3'>Kembali</a>
         </div>");
}

/* =======================================================
   OPSI 2 — HAPUS TOTAL PESANAN JIKA DIBATALKAN
=========================================================*/
if (isset($_GET['batal']) && $_GET['batal'] == '1' && $order_id) {

    // Hapus item pesanan
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$order_id'");

    // Hapus order
    mysqli_query($conn, "DELETE FROM orders WHERE id='$order_id'");

    echo "Pesanan dibatalkan dan dihapus.";
    exit();
}

/* =======================================================
   CEK STATUS — JIKA PESANAN SUDAH DIBATALKAN, TIDAK BISA BAYAR
=========================================================*/
$cekStatus = mysqli_query($conn, "SELECT * FROM orders WHERE id='$order_id'");
$order = mysqli_fetch_assoc($cekStatus);

if (!$order) {
    die("<div style='text-align:center;margin-top:50px;'>
            <h3>❌ Pesanan ini sudah dibatalkan dan dihapus!</h3>
            <a href='index.php' class='btn btn-primary mt-3'>Kembali</a>
         </div>");
}

$total = $order['total'] ?? 0;

/* =======================================================
   PROSES PEMBAYARAN
=========================================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Jika user mencoba confirm setelah dibatalkan
    $cekAgain = mysqli_query($conn, "SELECT id FROM orders WHERE id='$order_id'");
    if (mysqli_num_rows($cekAgain) == 0) {
        die("<h3>❌ Pesanan sudah dibatalkan!</h3>");
    }

    $metode = mysqli_real_escape_string($conn, $_POST['metode']);
    $vendor = mysqli_real_escape_string($conn, $_POST['vendor']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $jumlah = $total;

    $resi = 'RESI-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

    // Upload bukti pembayaran
    $bukti_path = null;
    if (!empty($_FILES['bukti']['name'])) {
        $target_dir = "uploads/bukti/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = basename($_FILES['bukti']['name']);
        $file_tmp = $_FILES['bukti']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_name = 'bukti_' . time() . '.' . $file_ext;
            $bukti_path = $target_dir . $new_name;
            move_uploaded_file($file_tmp, $bukti_path);
        } else {
            echo "<script>alert('❌ Format file tidak diizinkan!');</script>";
        }
    }

    $sql = "INSERT INTO payments (order_id, metode, status, tanggal, jumlah, vendor_pengirim, resi, bukti)
            VALUES ('$order_id', '$metode', 'Menunggu Konfirmasi Admin', NOW(), '$jumlah', '$vendor', '$resi', '$bukti_path')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        mysqli_query($conn, "UPDATE orders SET alamat_pengiriman='$alamat', status='Menunggu Konfirmasi Admin' WHERE id='$order_id'");
        header("Location: /marketplace_pakih/views/user/status_pembayaran.php?order_id=$order_id");
        exit();
    } else {
        echo "<script>alert('❌ Gagal menyimpan pembayaran: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pembayaran Pesanan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(120deg,#007bff,#00b4d8);min-height:100vh;}
.card{border:none;border-radius:15px;background:white;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
.btn-custom{background:#00b4d8;border:none;color:white;}
.btn-custom:hover{background:#007bff;}
.timer-box{
  background:#f8d7da;
  color:#842029;
  border:1px solid #f5c2c7;
  padding:10px;
  text-align:center;
  border-radius:10px;
  margin-bottom:15px;
}
</style>

<script>
// TIMER PEMBATALAN OTOMATIS
let waktu = 60;
let countdown = setInterval(function(){
    if (waktu > 0) {
        document.getElementById("timer").innerText = waktu + " detik";
        waktu--;
    } else {
        clearInterval(countdown);
        fetch("?batal=1&order_id=<?= $order_id ?>")
        .then(() => {
            alert("❌ Waktu habis! Pesanan dibatalkan.");
            window.location.href = "index.php";
        });
    }
}, 1000);

// BATALKAN PESANAN MANUAL
function batalkanPesanan(){
    if (confirm("Yakin ingin membatalkan pesanan?")) {
        fetch("?batal=1&order_id=<?= $order_id ?>")
        .then(() => {
            alert("❌ Pesanan berhasil dibatalkan.");
            window.location.href = "index.php";
        });
    }
}
</script>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background:linear-gradient(120deg,#007bff,#00b4d8);">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">🥨 Pangsisssst</a>
    <a href="index.php" class="btn btn-light btn-sm">← Kembali</a>
  </div>
</nav>

<div class="container py-5">
<div class="card p-4 mx-auto" style="max-width:650px;">
<h4 class="text-center text-primary mb-4">💳 Pembayaran Pesanan</h4>

<!-- Timer -->
<div class="timer-box">
⏰ Silahkan lakukan pembayaran dalam waktu <span id="timer">1 menit</span> atau pesanan akan dibatalkan otomatis.
</div>

<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">

  <div class="mb-3">
    <label class="form-label fw-semibold">Total Pembayaran</label>
    <input type="text" class="form-control" value="Rp <?= number_format($total,0,',','.'); ?>" readonly>
  </div>

  <div class="mb-3">
    <label class="form-label fw-semibold">Alamat Pengiriman</label>
    <textarea name="alamat" class="form-control" rows="3" required></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label fw-semibold">Metode Pembayaran</label>
    <select id="metode" name="metode" class="form-select" onchange="tampilkanInfoPembayaran()" required>
      <option value="">-- Pilih Metode --</option>
      <option value="QRIS">QRIS</option>
      <option value="Transfer Bank">Transfer Bank</option>
      <option value="Cash">COD</option>
    </select>
  </div>

  <div id="infoPembayaran"></div>

  <div class="mb-3 mt-3">
    <label class="form-label fw-semibold">Upload Bukti Pembayaran (opsional)</label>
    <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.gif">
  </div>

  <div class="mb-3">
    <label class="form-label fw-semibold">Pengiriman</label>
    <select name="vendor" class="form-select" required>
      <option value="">-- Pilih pengiriman --</option>
      <option value="Gojek">Gojek</option>
      <option value="Grab">Grab</option>
      <option value="JNE">JNE</option>
    </select>
  </div>

  <button type="submit" class="btn btn-custom w-100 mt-3">Konfirmasi Pembayaran</button>

  <!-- BUTTON BATAL -->
  <button type="button" onclick="batalkanPesanan()" class="btn btn-danger w-100 mt-2">
    ❌ Batalkan Pesanan
  </button>

</form>
</div>
</div>

<script>
function tampilkanInfoPembayaran(){
    const metode=document.getElementById("metode").value;
    const info=document.getElementById("infoPembayaran");
    let content="";
    if(metode==="Transfer Bank"){
        content=`<div class="alert alert-info mt-3">
        <strong>💰 Nomor Rekening:</strong><br>
        BCA: 1234567890 a.n Pangsisssst Store<br>
        Mandiri: 9876543210 a.n Pangsisssst Store</div>`;
    }else if(metode==="QRIS"){
        content=`<div class="alert alert-info mt-3 text-center">
        <strong>📱 Scan QRIS Berikut:</strong><br>
        <img src="assets/qris-baznas-5eda34a3d541df43ac060963.jpg" width="200"><br>
        <small>Setelah membayar, upload bukti pembayaran lalu klik konfirmasi.</small></div>`;
    }
    info.innerHTML=content;
}
</script>

</body>
</html>
