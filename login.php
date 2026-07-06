<?php
session_start();
require 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = $_POST['role'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($role === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['role'] = 'admin';
            $_SESSION['id']   = $user['id_admin'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            header("Location: admin/dashboard.php"); exit;
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE nim = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['role'] = 'user';
            $_SESSION['id']   = $user['id_pengguna'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            header("Location: user/dashboard.php"); exit;
        }
    }
    $error = 'Username atau password salah. Silakan coba lagi.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - UKM Kewirausahaan</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-page">
  <div class="login-wrap">

    <!-- KIRI: Branding -->
    <div class="login-left">
      <img src="assets/ukm_cendikia.png" alt="Logo UKM" style="width:80px;height:80px;object-fit:contain;margin-bottom:20px;">
      <h2>Sistem Penjadwalan UKM Kewirausahaan</h2>
      <div class="login-divider"></div>
      <p>Masuk untuk mengelola dan melihat jadwal kegiatan UKM Kewirausahaan Cendekia UNIPMA secara digital dan terstruktur.</p>
    </div>

    <!-- KANAN: Form Login -->
    <div class="login-right">
      <h3>Selamat Datang</h3>
      <p class="login-sub">Masukkan kredensial akun Anda untuk melanjutkan</p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Login Sebagai</label>
          <select name="role">
            <option value="user">👤 Anggota (NIM)</option>
            <option value="admin">🛡️ Administrator</option>
          </select>
        </div>
        <div class="form-group">
          <label>Username / NIM</label>
          <input type="text" name="username" placeholder="Masukkan username atau NIM" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" class="btn btn-blue">🔐 Masuk Sekarang</button>
        <div style="text-align:center; margin-top:16px;">
          <a href="lupa_password.php" style="font-size:0.82rem; color:var(--gray-text);">Lupa password?</a>
        </div>
      </form>
    </div>

  </div>
</div>
</body>
</html>
