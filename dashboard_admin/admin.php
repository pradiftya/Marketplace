<?php
session_start();
require_once "../config/koneksi.php";

// ==== TAMBAH PRODUK BARU ====
if (isset($_POST['tambah_produk'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $modal = mysqli_real_escape_string($conn, $_POST['modal']);
    $stok  = mysqli_real_escape_string($conn, $_POST['stok']);
    $gambar = "";

    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $gambar = time() . "_" . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar;
        move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file);
    }

    mysqli_query($conn, "INSERT INTO products (nama, harga, stok, gambar, modal) VALUES ('$nama','$harga','$stok','$gambar','$modal')");
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
$queryOrderProducts = "p.id DESC";
$queryWhereProducts = "";
$search_name = "";
$search_filter = "";
if (isset($_POST['cari_produk'])) {
    $search_name = $_POST['cari_nama']  ?? '';
    if (!empty($_POST['cari_nama'])) {
        $search_name = mysqli_real_escape_string($conn, $_POST['cari_nama']);
        $queryWhereProducts = "WHERE p.nama LIKE '%$search_name%'";
    }
    $search_filter = $_POST['cari_filter'] ?? '0';
    if ($search_filter == '1') {
        $queryOrderProducts = "sold DESC";
    } else if ($search_filter == '2') {
        $queryOrderProducts = "sold ASC";
    }
}
$queryFetchProducts = "SELECT p.*, COUNT(oi.id) AS sold FROM products p LEFT JOIN order_items oi ON oi.product_id = p.id $queryWhereProducts GROUP BY p.id, p.nama ORDER BY $queryOrderProducts";
$products = mysqli_query($conn, $queryFetchProducts);

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Pangsisssst Marketplace</title>
    <style>
        body {
            background: linear-gradient(120deg, #007bff, #00b4d8);
            min-height: 100vh;
            color: #212529;
            font-family: 'Segoe UI', sans-serif;
        }
        .card { border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        footer { text-align: center; color: white; margin-top: 40px; padding: 20px; }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include __DIR__ . '/navbar.php'; ?>

<div class="container mt-4">

    <h3 class="text-white mb-3">Kelola Produk</h3>

    <!-- FORM TAMBAH PRODUK BARU -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Tambah Produk Baru</h5>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="nama" class="form-control" placeholder="Nama Produk" required>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="modal" class="form-control" placeholder="Harga Modal" required>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="harga" class="form-control" placeholder="Harga Jual" required>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="stok" class="form-control" placeholder="Stok" required>
                    </div>

                    <div class="col-md-2">
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" name="tambah_produk" class="btn col-md-1 bg-primary text-white mt-3">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LIST PRODUK -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Cari Produk</h5>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">

                    <div class="col-md-8">
                        <input type="text" name="cari_nama" class="form-control" placeholder="Nama Produk" value="<?= htmlspecialchars($search_name) ?>">
                    </div>

                    <div class="col-md-3">
                        <select name="cari_filter" class="form-select">
                            <option value="0"<?= $search_filter == '0' ? 'selected' : '' ?>>Semua</option>
                            <option value="1"<?= $search_filter == '1' ? 'selected' : '' ?>>Paling Banyak Terjual</option>
                            <option value="2"<?= $search_filter == '2' ? 'selected' : '' ?>>Paling Sedikit Terjual</option>
                        </select>
                    </div>
                <button type="submit" name="cari_produk" class="btn col-md-1 bg-primary text-white mt-3">Cari</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($products)) : ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">

                <img src="../uploads/<?= $row['gambar']; ?>" class="card-img-top" style="height:200px;object-fit:cover;">

                <div class="card-body">
                    <h5><?= $row['nama']; ?></h5>
                    <p class="text-success fw-bold">Rp <?= number_format($row['harga']); ?></p>
                    <div class="row">
                        <p class="col-md-6">Stok : <b><?= $row['stok']; ?></b></p>
                        <p class="col-md-6 text-end"><?= $row['sold']; ?> Terjual</p>
                    </div>

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

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
