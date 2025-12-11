<?php
session_start();
include "config.php";

$error = "";

// Kalau sudah login, langsung lempar ke dashboard
if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 1. Validasi awal (field tidak boleh kosong)
    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {

        // 2. Amankan input
        $u = mysqli_real_escape_string($koneksi, $username);

        $sql    = "SELECT * FROM tb_user WHERE username = '$u' LIMIT 1";
        $result = mysqli_query($koneksi, $sql);

        if (!$result) {
            // 3. Error handling query
            $error = "Terjadi kesalahan saat mengakses data user.";
        } else {
            $user   = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user['password_hash'])) {
                // simpan ke session
                $_SESSION['id_user']      = $user['id_user'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['inisial']      = strtoupper(substr($user['nama_lengkap'], 0, 1));
                $_SESSION['role']         = $user['role'];

                if (isset($_POST['remember'])) {
                    setcookie("remember_user", $user['id_user'], time() + 30, "/");
                }

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Username atau password salah.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem Penjadwalan Kuliah</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-bg"></div>

    <div class="login-card">
        <div class="logo">
            <img src="assets/img/logo-uin.png" alt="Logo Kampus">
        </div>
        <h1>Sistem Penjadwalan Mata Kuliah</h1>

        <?php if ($error): ?>
            <p style="color:#fff;background:#b91c1c;padding:8px 12px;
                      border-radius:8px;font-size:13px;margin-bottom:12px;">
                <?= htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form action="index.php" method="post" class="need-validation">
            <div class="form-group">
                <label for="username">Username</label>
                <input class="form-control" type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input class="form-control" type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> Remember Me
                </label>

            </div>
            <button type="submit" class="btn btn-primary btn-login" style="display:block; margin:20px auto 0 auto;">Masuk</button>
        </form>
    </div>
</div>

<script src="assets/JS/app.js"></script>
</body>
</html>
