<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login / Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 50px; }
        .container { background: white; padding: 30px; width: 400px; margin: auto; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        form { display: none; }
        form.active { display: block; }
        input, select { width: 100%; padding: 8px; margin: 8px 0; }
        button { width: 100%; padding: 10px; background: #0066cc; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .switch { text-align: center; margin-top: 10px; }
        .switch a { color: #0066cc; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2 id="title">Login</h2>

    <!-- Form Login -->
    <form id="loginForm" class="active" method="POST" action="index.php?page=user&action=loginProcess">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <div class="switch">Belum punya akun? <a href="#" onclick="toggleForm('register')">Daftar</a></div>
    </form>

    <!-- Form Register -->
    <form id="registerForm" method="POST" action="index.php?page=user&action=registerProcess">
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <!-- Tambahan: Pilihan Role -->
        <select name="role" required>
            <option value="">-- Pilih Role --</option>
            <option value="admin">Admin</option>
            <option value="customer">User</option>
        </select>

        <button type="submit">Daftar</button>
        <div class="switch">Sudah punya akun? <a href="#" onclick="toggleForm('login')">Login</a></div>
    </form>
</div>

<script>
function toggleForm(type) {
    const login = document.getElementById('loginForm');
    const register = document.getElementById('registerForm');
    const title = document.getElementById('title');

    if (type === 'register') {
        login.classList.remove('active');
        register.classList.add('active');
        title.textContent = 'Register';
    } else {
        register.classList.remove('active');
        login.classList.add('active');
        title.textContent = 'Login';
    }
}
</script>
</body>
</html>
