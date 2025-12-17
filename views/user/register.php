<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - MYshop Pakih</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="width: 450px;">
        <h4 class="mb-3 text-center">Daftar Akun Baru</h4>

        <form action="proses_register.php" method="post">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Peran (Role)</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin">Admin</option>
                    <option value="customer">User</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Daftar</button>
            <p class="text-center mt-3 mb-0">Sudah punya akun? <a href="index.php">Login</a></p>
        </form>
    </div>
</div>

</body>
</html>
