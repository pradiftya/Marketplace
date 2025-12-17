<?php
include "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // ambil role dari form

    // Cek apakah email sudah digunakan
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah digunakan!'); window.location='register.php';</script>";
        exit();
    }

    // Simpan ke database
    $query = "INSERT INTO users (nama, email, password, role) 
              VALUES ('$nama', '$email', '$password', '$role')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal mendaftar!'); window.location='register.php';</script>";
    }
}
?>
