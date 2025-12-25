<?php
session_start();
require_once "./config/koneksi.php";

// ===== LOGOUT =====
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    echo "<script>alert('Anda telah logout.'); window.location='index.php';</script>";
exit();

    exit();
}

// ===== LOGIN =====
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        echo "<script>window.location='index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Email atau password salah!'); window.location='index.php';</script>";
        exit();
    }
}

// ===== REGISTER =====
if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? 'customer';

    if ($nama == "" || $email == "" || $password == "") {
        echo "<script>alert('Semua kolom wajib diisi!'); window.location='index.php';</script>";
        exit();
    }

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah digunakan!'); window.location='index.php';</script>";
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama','$email','$hashed','$role')");
    echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='index.php';</script>";
    exit();
}

// ===== CEK LOGIN =====
if (isset($_SESSION['user'])) {
  $user = $_SESSION['user'];
  if ($user['role'] == 'admin') {
      header("Location: dashboard_admin/admin.php");
      exit();
  }

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

  // === TAMBAH KE KERANJANG ===
  if (isset($_GET['add_to_cart'])) {
      $id = intval($_GET['add_to_cart']);
      $query = mysqli_prepare($conn, "SELECT id, nama, harga FROM products WHERE id=?");
      mysqli_stmt_bind_param($query, "i", $id);
      mysqli_stmt_execute($query);
      $result = mysqli_stmt_get_result($query);
      $product = mysqli_fetch_assoc($result);

      if ($product) {
          if (!isset($_SESSION['cart'])) {
              $_SESSION['cart'] = [];
          }
          if (isset($_SESSION['cart'][$id])) {
              $_SESSION['cart'][$id]['qty']++;
          } else {
              $_SESSION['cart'][$id] = [
                  'nama'  => $product['nama'],
                  'harga' => $product['harga'],
                  'qty'   => 1
              ];
          }
          echo "<script>alert('Produk berhasil ditambahkan!'); window.location='index.php?page=products';</script>";
          exit();
      } else {
          echo "<script>alert('Produk tidak ditemukan!'); window.location='index.php';</script>";
          exit();
      }
  }

  // === HAPUS DARI KERANJANG ===
  if (isset($_GET['remove_from_cart'])) {
      $id = (int) $_GET['remove_from_cart'];
      unset($_SESSION['cart'][$id]);
      echo "<script>alert('Produk dihapus dari keranjang!'); window.location='index.php?page=cart';</script>";
      exit();
  }

  // === CHECKOUT ===
  if (isset($_POST['checkout'])) {
      if (empty($_SESSION['cart'])) {
          echo "<script>alert('Keranjang masih kosong!');</script>";
      } else {
          $customer_id = $_SESSION['user']['id'];
          $total = 0;
          $tanggal = date("Y-m-d H:i:s");
          foreach ($_SESSION['cart'] as $item) {
              $total += $item['harga'] * $item['qty'];
          }
          $status = 'Menunggu Pembayaran';
          mysqli_query($conn, "INSERT INTO orders (customer_id, tanggal, status, total)
                                VALUES ('$customer_id', '$tanggal', '$status', '$total')");
          $order_id = mysqli_insert_id($conn);

          foreach ($_SESSION['cart'] as $id_produk => $item) {
              $qty = $item['qty'];
              $harga = $item['harga'];
              mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, harga)
                                    VALUES ('$order_id', '$id_produk', '$qty', '$harga')");
          }
          unset($_SESSION['cart']);
          header("Location: payment.php?order_id=$order_id");
          exit();
      }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pangsisssst Marketplace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
body { background: linear-gradient(120deg, #007bff, #00b4d8); min-height: 100vh; color: #212529; }
.navbar-custom { background: linear-gradient(120deg, #007bff, #00b4d8); box-shadow: 0 3px 8px rgba(0,0,0,0.2); }
.navbar-brand { font-weight: bold; color: #fff !important; letter-spacing: 1px; }
.nav-box { background-color: rgba(255,255,255,0.15); border-radius: 8px; padding: 6px 15px;
    color: #fff !important; font-weight: 500; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.3); margin-left: 10px; }
.nav-box:hover { background-color: #fff; color: #007bff !important; transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.2); }
.cart-badge { position: absolute; top: 4px; right: 6px; background: red; color: white; font-size: 12px;
    border-radius: 50%; padding: 2px 6px; }
.section { padding: 80px 20px; background: #fff; color: #333; }
.section:nth-child(even) { background: #f8f9fa; }
.btn-custom { background: #00b4d8; border: none; color: white; transition: 0.3s; }
.btn-custom:hover { background: #007bff; }
</style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="index.php">🥨 Pangsisssst Marketplace</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link nav-box" href="#" data-bs-toggle="modal" data-bs-target="#aboutModal">Tentang Kami</a></li>
        <?php if (isset($_SESSION['user']) && $user['role'] == 'customer'): ?>
          <li class="nav-item position-relative">
    <a class="nav-link nav-box position-relative" href="views/cart.php">🛒 Keranjang
        <?php if (!empty($_SESSION['cart'])): ?>
            <span class="cart-badge"><?= array_sum(array_column($_SESSION['cart'], 'qty')); ?></span>
        <?php endif; ?>
    </a>
    <?php  
// Cek apakah user punya pesanan terbaru
$cek_resi = mysqli_query($conn, "SELECT id FROM orders WHERE customer_id='{$user['id']}' ORDER BY id DESC LIMIT 1");
$data_resi = mysqli_fetch_assoc($cek_resi);
?>

<?php if ($data_resi): ?>
    <li class="nav-item">
        <a class="nav-link nav-box" href="views/resi.php?order_id=<?= $data_resi['id'] ?>">
            📦 Lihat Resi
        </a>
    </li>
<?php endif; ?>

</li>

        <?php endif; ?>
        <?php if (!isset($_SESSION['user'])): ?>
          <li class="nav-item"><a class="nav-link nav-box" href="#" data-bs-toggle="modal" data-bs-target="#authModal">Login / Register</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link nav-box" href="?action=logout">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ===== MODAL TENTANG KAMI ===== -->
<div class="modal fade" id="aboutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Tentang Pangsisssst Marketplace</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p class="lead">Pangsisssst Marketplace adalah platform yang menghadirkan camilan lokal dengan cita rasa autentik. 
        Kami berkomitmen membantu UMKM Indonesia berkembang melalui pemasaran digital.</p>
        <img src="assets/WhatsApp Image 2025-07-04 at 22.12.19_059b3c64.png" class="img-fluid rounded mt-3" alt="Tentang Kami">
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL LOGIN / REGISTER ===== -->
<div class="modal fade" id="authModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="authModalLabel">Login</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="loginForm" method="POST">
          <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
          <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
          <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Login</button>
        </form>
       <form id="registerForm" method="POST" style="display:none;">
    <input type="text" name="nama" class="form-control mb-3" placeholder="Nama Lengkap" required>

    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>

    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

    <!-- Tambahan Pilihan Role -->
   <select name="role" class="form-control mb-3" required>
    <option value="">-- Pilih Role --</option>
    <option value="customer">Customer</option>
    <option value="admin">Admin</option>
</select>


    <button type="submit" name="register" class="btn btn-success w-100">Register</button>
</form>

        <p class="text-center mt-3">
          <a href="#" id="toggleFormLink" onclick="toggleForm()">Belum punya akun? Daftar di sini</a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php if (!isset($_SESSION['user'])): ?>
  <!-- ===== HALAMAN WELCOME ===== -->
  <header class="text-center text-white py-5">
    <h1>Selamat Datang di <b>Pangsisssst Marketplace</b></h1>
    <p class="lead">Temukan snack terbaik dengan harga bersahabat!</p>
  </header>
  <section id="produk" class="section text-center">
    <div class="container">
      <h2 class="fw-bold mb-4 text-primary">Produk Unggulan Kami</h2>
      <div class="row justify-content-center">
        <div class="col-md-3 mb-4">
          <div class="card shadow-sm">
            <img src="uploads/1761034144_Foto produk_keripik pangsissst-139 - Copy.JPG" class="card-img-top" style="height:200px;object-fit:cover;">
            <div class="card-body"><h5>Keripik Pangsit </h5><p class="text-muted">Pedasnya nagih, gurihnya pas!</p></div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="card shadow-sm">
            <img src="uploads/Foto produk_keripik pangsissst-119 - Copy.JPG" class="card-img-top" style="height:200px;object-fit:cover;">
            <div class="card-body"><h5>Keripik Singkong Pedas</h5><p class="text-muted">Teman setia saat nonton film.</p></div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="card shadow-sm">
            <img src="uploads/1761040446_Foto produk_keripik pangsissst-088.JPG" class="card-img-top" style="height:200px;object-fit:cover;">
            <div class="card-body"><h5>Makaroni Goreng</h5><p class="text-muted">Cita rasa tradisional khas Nusantara.</p></div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php else: ?>
  <!-- ===== HALAMAN CUSTOMER (PRODUK DINAMIS) ===== -->
  <div class="container mt-5">
    <h3 class="text-white mb-4">Hai, <?= htmlspecialchars($user['nama']); ?> 👋 Temukan produk terbaik kami!</h3>

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
                <button type="submit" name="cari_produk" class="btn col-md-1 btn-custom mt-3">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
      <?php while ($row = mysqli_fetch_assoc($products)) : ?>
        <div class="col-md-3 mb-4">
          <div class="card shadow-sm">
            <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>" class="card-img-top" style="height:200px;object-fit:cover;">
            <div class="card-body">
              <h5><?= htmlspecialchars($row['nama']); ?></h5>
              <p class="text-success fw-bold mb-1">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></p>
              <div class="row">
                  <p class="col-md-6">Stok : <b><?= $row['stok']; ?></b></p>
                  <p class="col-md-6 text-end"><?= $row['sold']; ?> Terjual</p>
              </div>
              <?php if ($row['stok'] > 0): ?>
                  <a href="index.php?add_to_cart=<?= $row['id']; ?>" 
                    class="btn btn-custom btn-sm w-100">+ Keranjang</a>
              <?php else: ?>
                  <button class="btn btn-secondary btn-sm w-100" disabled>Stok Habis</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
<?php endif; ?>

<!-- ===== FOOTER ===== -->
<footer class="text-center text-white mt-5 py-4" style="background: linear-gradient(120deg,#007bff,#00b4d8);">
  <div class="container">
    <h5 class="fw-bold mb-3">Hubungi Kami</h5>
    <div class="d-flex justify-content-center gap-3 mb-3">
      <a href="https://wa.me/6285775862451" target="_blank" class="text-white text-decoration-none fs-5"><i class="bi bi-whatsapp"></i> WhatsApp</a>
      <a href="https://instagram.com/keripik_pangsissst" target="_blank" class="text-white text-decoration-none fs-5"><i class="bi bi-instagram"></i> Instagram</a>
    </div>
    <p class="mb-0 small">© <?= date("Y"); ?> Pangsisssst Marketplace. Semua Hak Dilindungi.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleForm() {
  const loginForm = document.getElementById('loginForm');
     const registerForm = document.getElementById('registerForm');
       const link = document.getElementById('toggleFormLink');
   const title = document.getElementById('authModalLabel');
  if (loginForm.style.display === 'none') {
    loginForm.style.display = 'block';
    registerForm.style.display = 'none';
    link.innerText = 'Belum punya akun? Daftar di sini';
    title.innerText = 'Login';
  } else {
    loginForm.style.display = 'none';
    registerForm.style.display = 'block';
    link.innerText = 'Sudah punya akun? Login di sini';
    title.innerText = 'Register';
  }
}
</script>
</body>
</html>
