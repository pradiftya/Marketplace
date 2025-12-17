<?php
session_start();
require_once "../config/koneksi.php";

// ==== Proteksi akses hanya untuk admin ====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
   echo "<script>alert('Anda telah logout.'); window.location='../index.php';</script>";

    exit();
}

$user = $_SESSION['user'];

// ==== LOGOUT ====
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    echo "<script>alert('Anda telah logout.'); window.location='../index.php';</script>";
    exit();
}

// ==== TAMBAH PRODUK BARU ====
if (isset($_POST['tambah_produk'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $stok  = mysqli_real_escape_string($conn, $_POST['stok']);
    $gambar = "";

    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $gambar = time() . "_" . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar;
        move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file);
    }

    mysqli_query($conn, "INSERT INTO products (nama, harga, stok, gambar) 
                         VALUES ('$nama','$harga','$stok','$gambar')");
    echo "<script>alert('Produk berhasil ditambahkan!'); window.location='admin.php';</script>";
    exit();
}

// ==== HAPUS PRODUK ====
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $cek = mysqli_query($conn, "SELECT * FROM order_items WHERE product_id=$id");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Produk ini tidak dapat dihapus karena sudah ada dalam transaksi.'); window.location='admin.php';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM products WHERE id=$id");
        echo "<script>alert('Produk dihapus!'); window.location='admin.php';</script>";

    }
    exit();
}

// ==== TAMBAH STOK PRODUK ====
if (isset($_POST['tambah_stok'])) {
    $id_produk = (int) $_POST['id_produk'];
    $stok_tambah = (int) $_POST['stok_tambah'];

    mysqli_query($conn, "UPDATE products SET stok = stok + $stok_tambah WHERE id = $id_produk");

    echo "<script>alert('Stok berhasil ditambahkan!'); window.location='admin.php';</script>";
    exit();
}

// ==== AMBIL DATA PRODUK ====
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Pangsisssst Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(120deg, #007bff, #00b4d8);
            min-height: 100vh;
            color: #212529;
            font-family: 'Segoe UI', sans-serif;
        }
        .navbar-custom {
            background: linear-gradient(120deg, #007bff, #00b4d8);
        }
        .btn-custom { background: #00b4d8; color: white; border: none; }
        .btn-custom:hover { background: #007bff; }
        .card { border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        footer { text-align: center; color: white; margin-top: 40px; padding: 20px; }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="admin.php">Pangsisssst Marketplace (Admin)</a>

    <div class="d-flex align-items-center">
      <a href="statistik.php" class="btn btn-light btn-sm me-2">📊 Statistik</a>
      <a href="histori.php" class="btn btn-info btn-sm me-2">🧾 Histori Pembelian</a>
      <a href="pesanan.php" class="btn btn-warning btn-sm me-2">📦 Lihat Pesanan</a>
      <a href="laporan_keuangan.php" class="btn btn-warning btn-sm me-2">📑 Laporan Keuangan</a>

      <span class="navbar-text text-white me-3">
        Halo, <?= htmlspecialchars($user['nama']); ?> (Admin)
      </span>

      <a href="?action=logout" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">

    <h3 class="text-white mb-3">Kelola Produk</h3>

    <!-- FORM TAMBAH PRODUK BARU -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Tambah Produk Baru</h5>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">

                    <div class="col-md-4">
                        <input type="text" name="nama" class="form-control" placeholder="Nama Produk" required>
                    </div>

                    <div class="col-md-3">
                        <input type="number" name="harga" class="form-control" placeholder="Harga" required>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="stok" class="form-control" placeholder="Stok Awal" required>
                    </div>

                    <div class="col-md-3">
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                    </div>

                </div>
                <button type="submit" name="tambah_produk" class="btn btn-custom mt-3">Tambah Produk</button>
            </form>
        </div>
    </div>

    <!-- LIST PRODUK -->
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($products)) : ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">

                <img src="../uploads/<?= $row['gambar']; ?>" class="card-img-top" style="height:200px;object-fit:cover;">

                <div class="card-body">
                    <h5><?= $row['nama']; ?></h5>
                    <p class="text-success fw-bold">Rp <?= number_format($row['harga']); ?></p>
                    <p>Stok: <b><?= $row['stok']; ?></b></p>

                    <!-- BUTTON TAMBAH STOK -->
                    <button class="btn btn-primary btn-sm w-100 mb-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalStok<?= $row['id']; ?>">
                        ➕ Tambah Stok
                    </button>

                    <!-- BUTTON HAPUS -->
                    <a href="?hapus=<?= $row['id']; ?>" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')"
                       class="btn btn-danger btn-sm w-100">
                       Hapus
                    </a>
                </div>
            </div>
        </div>

        <!-- MODAL TAMBAH STOK -->
        <div class="modal fade" id="modalStok<?= $row['id']; ?>">
          <div class="modal-dialog">
            <form method="POST" class="modal-content">

              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Stok - <?= $row['nama']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <input type="hidden" name="id_produk" value="<?= $row['id']; ?>">
                <label>Jumlah Stok Baru</label>
                <input type="number" name="stok_tambah" class="form-control" required min="1">
              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah_stok" class="btn btn-primary">Tambah</button>
              </div>

            </form>
          </div>
        </div>

        <?php endwhile; ?>
    </div>
</div>

<footer>
    © <?= date('Y'); ?> Pangsisssst Marketplace | Admin Panel
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
