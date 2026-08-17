<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}
require '../config/db.php';
require '../config/jadwal_helper.php';

$id     = intval($_GET['id']);
$q      = $pdo->prepare("SELECT * FROM detail_jadwal WHERE id_detail_jadwal=?");
$q->execute([$id]);
$data   = $q->fetch();
if (!$data) { header("Location: dashboard.php"); exit; }

$anggota = $pdo->query("SELECT * FROM pengguna ORDER BY nama_lengkap")->fetchAll();
$jenis   = $pdo->query("SELECT * FROM jenis_jadwal")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // tampilkan kembali input admin bila validasi gagal
    $data['id_pengguna']     = $_POST['id_pengguna'];
    $data['id_jenis_jadwal'] = $_POST['id_jenis_jadwal'];
    $data['tanggal_tugas']   = $_POST['tanggal_tugas'];
    $data['waktu_mulai']     = $_POST['waktu_mulai'];
    $data['waktu_selesai']   = $_POST['waktu_selesai'];
    $data['status_tugas']    = $_POST['status_tugas'];

    // jadwal yang sedang diedit dikecualikan agar tidak bentrok dengan dirinya sendiri
    $error = validasiJadwal(
        $pdo, $data['id_pengguna'], $data['tanggal_tugas'],
        $data['waktu_mulai'], $data['waktu_selesai'], $id
    );

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE detail_jadwal SET
            id_pengguna=?, id_jenis_jadwal=?, tanggal_tugas=?,
            waktu_mulai=?, waktu_selesai=?, status_tugas=?
            WHERE id_detail_jadwal=?");
        $stmt->execute([
            $data['id_pengguna'], $data['id_jenis_jadwal'],
            $data['tanggal_tugas'], $data['waktu_mulai'],
            $data['waktu_selesai'], $data['status_tugas'], $id
        ]);
        header("Location: dashboard.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Jadwal - Admin</title>
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
        <h2>Edit Jadwal</h2>
        <p>Ubah detail jadwal tugas anggota</p>
      </div>
      <div class="topbar-actions">
        <a href="dashboard.php" class="btn btn-sm" style="background:var(--gray-light);color:var(--text-dark);border:1px solid var(--gray-mid);">← Kembali</a>
      </div>
    </header>

    <main class="page-content">
      <div class="card" style="max-width:580px;">
        <div class="card-header">
          <h3>✏️ Form Edit Jadwal</h3>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label>Pilih Anggota</label>
            <select name="id_pengguna" required>
              <?php foreach ($anggota as $a): ?>
                <option value="<?= $a['id_pengguna'] ?>" <?= $data['id_pengguna']==$a['id_pengguna']?'selected':'' ?>>
                  <?= htmlspecialchars($a['nama_lengkap']) ?> (<?= $a['nim'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Jenis Jadwal</label>
            <select name="id_jenis_jadwal" required>
              <?php foreach ($jenis as $j): ?>
                <option value="<?= $j['id_jenis_jadwal'] ?>" <?= $data['id_jenis_jadwal']==$j['id_jenis_jadwal']?'selected':'' ?>>
                  <?= htmlspecialchars($j['nama_jenis']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Tanggal Tugas</label>
            <input type="date" name="tanggal_tugas" value="<?= $data['tanggal_tugas'] ?>" required>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label>Waktu Mulai</label>
              <input type="time" name="waktu_mulai" value="<?= $data['waktu_mulai'] ?>" required>
            </div>
            <div class="form-group">
              <label>Waktu Selesai</label>
              <input type="time" name="waktu_selesai" value="<?= $data['waktu_selesai'] ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status_tugas">
              <option <?= $data['status_tugas']==='Belum Selesai'?'selected':'' ?>>Belum Selesai</option>
              <option <?= $data['status_tugas']==='Selesai'?'selected':'' ?>>Selesai</option>
            </select>
          </div>
          <button type="submit" class="btn btn-blue" style="width:100%;justify-content:center;margin-top:8px;">
            💾 Simpan Perubahan
          </button>
        </form>
      </div>
    </main>
  </div>
</div>
</body>
</html>
