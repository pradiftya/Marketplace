<?php
    require_once "../config/koneksi.php";

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
        header("Location: ../index.php");
        exit();
    }

    $user = $_SESSION['user'];

    // ==== LOGOUT ====
    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        session_unset();
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .navbar-custom {
            background: linear-gradient(120deg, #007bff, #00b4d8);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm px-5 fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin.php">Pangsisssst Marketplace (Admin)</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                🏪 Toko
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="pesanan.php">👨‍👩‍👦‍👦 Penggajian</a>
                </li>
            </ul>
            </li>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                📋 Pesanan
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="pesanan.php">📋 Lihat Pesanan</a>
                </li>
            </ul>
            </li>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                🧾 Laporan
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="statistik.php">📊 Statistik</a>
                </li>
                <li>
                    <a class="dropdown-item" href="histori.php">🧾 Histori Pembelian</a>
                </li>
                <li>
                    <a class="dropdown-item" href="laporan_keuangan.php">📑 Laporan Keuangan</a>
                </li>
            </ul>
            </li>
        </ul>
        <form class="d-flex" role="search">
            <span class="navbar-text text-white me-3">
                Halo, <?= htmlspecialchars($user['nama']); ?> (Admin)
            </span>
            <a href="?action=logout" class="btn btn-outline-light">Logout</a>
        </form>
        </div>
    </div>
    </nav>
</body>
</html>