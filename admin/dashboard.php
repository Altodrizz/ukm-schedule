<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}
require '../config/db.php';

$stmt = $pdo->query("
    SELECT dj.*, p.nama_lengkap AS nama_anggota, jj.nama_jenis, a.nama_lengkap AS nama_admin
    FROM detail_jadwal dj
    JOIN pengguna p ON dj.id_pengguna = p.id_pengguna
    JOIN jenis_jadwal jj ON dj.id_jenis_jadwal = jj.id_jenis_jadwal
    JOIN admin a ON dj.id_admin = a.id_admin
    ORDER BY dj.tanggal_tugas DESC
");
$jadwals = $stmt->fetchAll();
$total   = count($jadwals);
$selesai = count(array_filter($jadwals, fn($j) => $j['status_tugas'] === 'Selesai'));
$belum   = $total - $selesai;
$anggota_count = $pdo->query("SELECT COUNT(*) FROM pengguna")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - UKM</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="../assets/ukm_cendikia.png" alt="Logo UKM" style="width:42px;height:42px;object-fit:contain;border-radius:8px;">
      <div class="brand-text">
        <div class="brand-name">JADWAL UKM</div>
        <div class="brand-sub">Kewirausahaan</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <p class="nav-label">Menu Utama</p>
      <a href="dashboard.php" class="nav-item active">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <a href="tambah_jadwal.php" class="nav-item">
        <span class="nav-icon">➕</span> Tambah Jadwal
      </a>
      <p class="nav-label">Master Data</p>
<a href="kelola_anggota.php" class="nav-item">
  <span class="nav-icon">👥</span> Kelola Anggota
</a>
<a href="kelola_jenis.php" class="nav-item">
  <span class="nav-icon">🏷️</span> Jenis Kegiatan
</a>
      <p class="nav-label">Sistem</p>
      <a href="../logout.php" class="nav-item">
        <span class="nav-icon">🚪</span> Logout
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
        <div class="user-detail">
          <div class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
          <div class="user-role">Administrator</div>
        </div>
      </div>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <div class="topbar-title">
        <h2>Dashboard Admin</h2>
        <p>Selamat datang kembali, <?= htmlspecialchars($_SESSION['nama']) ?></p>
      </div>
      <div class="topbar-actions">
        <a href="tambah_jadwal.php" class="btn btn-blue btn-sm">➕ Tambah Jadwal</a>
      </div>
    </header>

    <main class="page-content">
      <!-- STAT CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">📅</div>
          <div class="stat-info">
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Total Jadwal</div>
          </div>
        </div>
        <div class="stat-card green">
          <div class="stat-icon green">✅</div>
          <div class="stat-info">
            <div class="stat-value"><?= $selesai ?></div>
            <div class="stat-label">Selesai</div>
          </div>
        </div>
        <div class="stat-card red">
          <div class="stat-icon red">⏳</div>
          <div class="stat-info">
            <div class="stat-value"><?= $belum ?></div>
            <div class="stat-label">Belum Selesai</div>
          </div>
        </div>
        <div class="stat-card gold">
          <div class="stat-icon gold">👥</div>
          <div class="stat-info">
            <div class="stat-value"><?= $anggota_count ?></div>
            <div class="stat-label">Total Anggota</div>
          </div>
        </div>
      </div>

      <!-- TABLE -->
      <div class="card">
        <div class="card-header">
          <h3>📋 Daftar Semua Jadwal</h3>
          <a href="tambah_jadwal.php" class="btn btn-blue btn-sm">+ Tambah</a>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>No</th><th>Anggota</th><th>Jenis Tugas</th>
                <th>Tanggal</th><th>Waktu</th><th>Status</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($jadwals)): ?>
              <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--gray-text);">
                Belum ada jadwal. <a href="tambah_jadwal.php">Tambah sekarang</a>
              </td></tr>
              <?php else: ?>
              <?php foreach ($jadwals as $i => $j): ?>
              <tr>
                <td style="color:var(--gray-text);font-weight:600;"><?= $i+1 ?></td>
                <td><strong><?= htmlspecialchars($j['nama_anggota']) ?></strong></td>
                <td><?= htmlspecialchars($j['nama_jenis']) ?></td>
                <td><?= date('d M Y', strtotime($j['tanggal_tugas'])) ?></td>
                <td><?= substr($j['waktu_mulai'],0,5) ?> – <?= substr($j['waktu_selesai'],0,5) ?></td>
                <td>
                  <span class="badge <?= $j['status_tugas']==='Selesai' ? 'green':'red' ?>">
                    <?= $j['status_tugas'] ?>
                  </span>
                </td>
                <td>
                  <a href="edit_jadwal.php?id=<?= $j['id_detail_jadwal'] ?>" class="action-link edit">Edit</a>
                  <a href="hapus_jadwal.php?id=<?= $j['id_detail_jadwal'] ?>" class="action-link delete"
                     onclick="return confirm('Yakin ingin menghapus jadwal ini?')">Hapus</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>
</body>
</html>
