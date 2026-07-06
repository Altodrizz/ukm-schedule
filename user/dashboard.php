<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php"); exit;
}
require '../config/db.php';

$stmt = $pdo->prepare("
    SELECT dj.*, jj.nama_jenis
    FROM detail_jadwal dj
    JOIN jenis_jadwal jj ON dj.id_jenis_jadwal = jj.id_jenis_jadwal
    WHERE dj.id_pengguna = ?
    ORDER BY dj.tanggal_tugas ASC
");
$stmt->execute([$_SESSION['id']]);
$jadwals = $stmt->fetchAll();
$selesai = count(array_filter($jadwals, fn($j) => $j['status_tugas']==='Selesai'));
$belum   = count($jadwals) - $selesai;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Saya - UKM</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body style="background:var(--gray-light);">

<!-- NAVBAR -->
<nav class="navbar">
  <div class="navbar-inner">
    <div class="navbar-brand">
      <img src="../assets/ukm_cendikia.png" alt="Logo UKM" style="width:48px;height:48px;object-fit:contain;">
      <div class="navbar-brand-text">
        <div class="brand-name">UKM Kewirausahaan</div>
        <div class="brand-sub">Universitas PGRI Madiun</div>
      </div>
    </div>
    <div class="navbar-user">
      <div class="user-chip">
        <div class="chip-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
        <?= htmlspecialchars($_SESSION['nama']) ?>
      </div>
      <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<!-- KONTEN -->
<div class="page-wrapper">
  <!-- STAT CARDS -->
  <div class="stats-grid" style="margin-bottom:28px;">
    <div class="stat-card">
      <div class="stat-icon blue">📅</div>
      <div class="stat-info">
        <div class="stat-value"><?= count($jadwals) ?></div>
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
  </div>

  <!-- TABLE -->
  <div class="card">
    <div class="card-header">
      <h3>📋 Jadwal Tugas Saya</h3>
    </div>
    <div class="table-wrapper">
      <?php if (empty($jadwals)): ?>
        <p style="text-align:center;padding:40px;color:var(--gray-text);">
          Belum ada jadwal yang ditetapkan untuk kamu.
        </p>
      <?php else: ?>
      <table>
        <thead>
          <tr><th>No</th><th>Jenis Tugas</th><th>Tanggal</th><th>Waktu</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($jadwals as $i => $j): ?>
          <tr>
            <td style="color:var(--gray-text);font-weight:600;"><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($j['nama_jenis']) ?></strong></td>
            <td><?= date('d M Y', strtotime($j['tanggal_tugas'])) ?></td>
            <td><?= substr($j['waktu_mulai'],0,5) ?> – <?= substr($j['waktu_selesai'],0,5) ?></td>
            <td>
              <span class="badge <?= $j['status_tugas']==='Selesai'?'green':'red' ?>">
                <?= $j['status_tugas'] ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer class="footer">
  <p>&copy; <?= date('Y') ?> UKM Kewirausahaan Cendekia &mdash; UNIPMA</p>
</footer>

</body>
</html>
