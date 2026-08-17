<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}
require '../config/db.php';
require '../config/jadwal_helper.php';

$anggota = $pdo->query("SELECT * FROM pengguna ORDER BY nama_lengkap")->fetchAll();
$jenis   = $pdo->query("SELECT * FROM jenis_jadwal")->fetchAll();
$pesan   = '';
$error   = '';
$isi     = ['id_pengguna'=>'', 'id_jenis_jadwal'=>'', 'tanggal_tugas'=>'', 'waktu_mulai'=>'', 'waktu_selesai'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($isi as $k => $v) { $isi[$k] = $_POST[$k] ?? ''; }

    $error = validasiJadwal(
        $pdo, $isi['id_pengguna'], $isi['tanggal_tugas'],
        $isi['waktu_mulai'], $isi['waktu_selesai']
    );

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO detail_jadwal
            (id_pengguna, id_jenis_jadwal, id_admin, tanggal_tugas, waktu_mulai, waktu_selesai)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $isi['id_pengguna'], $isi['id_jenis_jadwal'], $_SESSION['id'],
            $isi['tanggal_tugas'], $isi['waktu_mulai'], $isi['waktu_selesai']
        ]);
        $pesan = "Jadwal berhasil ditambahkan!";
        $isi = array_fill_keys(array_keys($isi), '');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Jadwal - Admin</title>
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
      <a href="dashboard.php" class="nav-item">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <a href="tambah_jadwal.php" class="nav-item active">
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
        <h2>Tambah Jadwal Baru</h2>
        <p>Tambahkan jadwal tugas untuk anggota UKM</p>
      </div>
      <div class="topbar-actions">
        <a href="dashboard.php" class="btn btn-sm" style="background:var(--gray-light);color:var(--text-dark);border:1px solid var(--gray-mid);">← Kembali</a>
      </div>
    </header>

    <main class="page-content">
      <div class="card" style="max-width:580px;">
        <div class="card-header">
          <h3>📝 Form Tambah Jadwal</h3>
        </div>

        <?php if ($pesan): ?>
          <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label>Pilih Anggota</label>
            <select name="id_pengguna" required>
              <option value="" disabled <?= $isi['id_pengguna']===''?'selected':'' ?>>-- Pilih Anggota --</option>
              <?php foreach ($anggota as $a): ?>
                <option value="<?= $a['id_pengguna'] ?>" <?= (string)$isi['id_pengguna']===(string)$a['id_pengguna']?'selected':'' ?>><?= htmlspecialchars($a['nama_lengkap']) ?> (<?= $a['nim'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Jenis Jadwal</label>
            <select name="id_jenis_jadwal" required>
              <option value="" disabled <?= $isi['id_jenis_jadwal']===''?'selected':'' ?>>-- Pilih Jenis --</option>
              <?php foreach ($jenis as $j): ?>
                <option value="<?= $j['id_jenis_jadwal'] ?>" <?= (string)$isi['id_jenis_jadwal']===(string)$j['id_jenis_jadwal']?'selected':'' ?>><?= htmlspecialchars($j['nama_jenis']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Tanggal Tugas</label>
            <input type="date" name="tanggal_tugas" value="<?= htmlspecialchars($isi['tanggal_tugas']) ?>" required>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label>Waktu Mulai</label>
              <input type="time" name="waktu_mulai" value="<?= htmlspecialchars($isi['waktu_mulai']) ?>" required>
            </div>
            <div class="form-group">
              <label>Waktu Selesai</label>
              <input type="time" name="waktu_selesai" value="<?= htmlspecialchars($isi['waktu_selesai']) ?>" required>
            </div>
          </div>
          <button type="submit" class="btn btn-blue" style="width:100%;justify-content:center;margin-top:8px;">
            💾 Simpan Jadwal
          </button>
        </form>
      </div>
    </main>
  </div>
</div>
</body>
</html>
