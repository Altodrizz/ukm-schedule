<?php
session_start();
require 'config/db.php';

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = $_POST['role'];
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama']);
    $pass_baru= $_POST['password_baru'];

    if (empty($username) || empty($nama) || empty($pass_baru)) {
        $error = 'Semua kolom harus diisi!';
    } else {
        $hash = password_hash($pass_baru, PASSWORD_BCRYPT);
        if ($role === 'admin') {
            $cek = $pdo->prepare("SELECT * FROM admin WHERE username=? AND nama_lengkap=?");
            $cek->execute([$username, $nama]);
            if ($cek->fetch()) {
                $pdo->prepare("UPDATE admin SET password=? WHERE username=?")->execute([$hash, $username]);
                $success = 'Password berhasil direset. Silakan login.';
            } else { $error = 'Username atau Nama Lengkap tidak cocok!'; }
        } else {
            $cek = $pdo->prepare("SELECT * FROM pengguna WHERE nim=? AND nama_lengkap=?");
            $cek->execute([$username, $nama]);
            if ($cek->fetch()) {
                $pdo->prepare("UPDATE pengguna SET password=? WHERE nim=?")->execute([$hash, $username]);
                $success = 'Password berhasil direset. Silakan login.';
            } else { $error = 'NIM atau Nama Lengkap tidak cocok!'; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password - UKM</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-page">
  <div class="login-wrap">
    <div class="login-left">
      <div class="login-left-logo">🔑</div>
      <h2>Reset Password Akun Anda</h2>
      <div class="login-divider"></div>
      <p>Masukkan data verifikasi yang sesuai dengan data pendaftaran akun Anda untuk mereset password.</p>
    </div>
    <div class="login-right">
      <h3>Reset Password</h3>
      <p class="login-sub">Verifikasi identitas Anda terlebih dahulu</p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <a href="login.php" class="btn btn-blue" style="width:100%;justify-content:center;margin-top:8px;">Kembali ke Login</a>
      <?php else: ?>
      <form method="POST">
        <div class="form-group">
          <label>Tipe Akun</label>
          <select name="role">
            <option value="user">Anggota (NIM)</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="form-group">
          <label>Username / NIM</label>
          <input type="text" name="username" placeholder="Username atau NIM" required>
        </div>
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" placeholder="Nama lengkap sesuai data" required>
        </div>
        <div class="form-group">
          <label>Password Baru</label>
          <input type="password" name="password_baru" placeholder="Buat password baru" required>
        </div>
        <button type="submit" class="btn btn-blue" style="width:100%;justify-content:center;">🔒 Simpan Password Baru</button>
        <div style="text-align:center;margin-top:14px;">
          <a href="login.php" style="font-size:0.82rem;color:var(--gray-text);">← Kembali ke Login</a>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
