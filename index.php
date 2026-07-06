<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UKM Kewirausahaan Cendekia</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- TOP STRIP -->
<div class="topstrip">
  <div class="topstrip-inner">
    <span>📍 Universitas PGRI Madiun (UNIPMA)</span>
    <span>📧 ukm.kewirausahaan@unipma.ac.id</span>
  </div>
</div>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="navbar-inner">
    <div class="navbar-brand">
      <img src="assets/ukm_cendikia.png" alt="Logo UKM" style="width:48px;height:48px;object-fit:contain;">
      <div class="navbar-brand-text">
        <div class="brand-name">UKM Kewirausahaan</div>
        <div class="brand-sub">Universitas PGRI Madiun</div>
      </div>
    </div>
    <ul class="navbar-menu">
      <li><a href="index.php" class="active">Beranda</a></li>
      <li><a href="login.php">Login</a></li>
    </ul>
    <div>
      <a href="login.php" class="btn btn-blue btn-sm">Masuk Sistem</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <span class="hero-eyebrow">Sistem Penjadwalan Digital</span>
    <h1>Jadwal Kegiatan UKM<br>Kewirausahaan Cendekia</h1>
    <p>Kelola jadwal piket, belanja, dan rapat UKM secara mudah, terstruktur, dan transparan untuk seluruh anggota.</p>
    <div class="hero-buttons">
      <a href="login.php" class="btn btn-primary">Masuk Sekarang</a>
      <a href="#tentang" class="btn btn-outline">Pelajari Lebih</a>
    </div>
  </div>
</section>

<!-- FEATURE CARDS -->
<section class="section section-alt" id="tentang">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Fitur Utama</div>
      <h2>Apa yang Bisa Kamu Lakukan?</h2>
      <div class="divider-gold"></div>
      <p>Sistem ini dirancang untuk mempermudah koordinasi jadwal antar anggota UKM Kewirausahaan.</p>
    </div>
    <div class="cards-grid">
      <div class="feature-card">
        <div class="feature-icon">📅</div>
        <h3>Penjadwalan Terstruktur</h3>
        <p>Admin dapat menambahkan, mengubah, dan menghapus jadwal tugas anggota kapan saja dengan mudah.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">👥</div>
        <h3>Multi Peran Pengguna</h3>
        <p>Sistem mendukung dua peran: Admin sebagai pengelola dan Anggota sebagai penerima jadwal tugas.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Pantau Status Tugas</h3>
        <p>Setiap jadwal dilengkapi status penyelesaian sehingga koordinasi antar anggota lebih transparan.</p>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <p>&copy; <?= date('Y') ?> UKM Kewirausahaan Cendekia &mdash; Universitas PGRI Madiun. All rights reserved.</p>
</footer>

</body>
</html>
