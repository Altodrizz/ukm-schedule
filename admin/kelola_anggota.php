<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}
require '../config/db.php';

$pesan = ''; $error = '';

// TAMBAH
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nim   = trim($_POST['nim']);
    $nama  = trim($_POST['nama_lengkap']);
    $pass  = $_POST['password'];

    $cek = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE nim = ?");
    $cek->execute([$nim]);
    if ($cek->fetch()) {
        $error = "NIM $nim sudah terdaftar!";
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO pengguna (nim, nama_lengkap, password) VALUES (?,?,?)")
            ->execute([$nim, $nama, $hash]);
        $pesan = "Anggota '$nama' berhasil ditambahkan!";
    }
}

// HAPUS
if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM pengguna WHERE id_pengguna = ?")->execute([intval($_GET['hapus'])]);
    header("Location: kelola_anggota.php?deleted=1"); exit;
}

// EDIT — simpan
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id   = intval($_POST['id_pengguna']);
    $nim  = trim($_POST['nim']);
    $nama = trim($_POST['nama_lengkap']);
    $pass = $_POST['password'];

    if (!empty($pass)) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE pengguna SET nim=?, nama_lengkap=?, password=? WHERE id_pengguna=?")
            ->execute([$nim, $nama, $hash, $id]);
    } else {
        $pdo->prepare("UPDATE pengguna SET nim=?, nama_lengkap=? WHERE id_pengguna=?")
            ->execute([$nim, $nama, $id]);
    }
    $pesan = "Data anggota berhasil diperbarui!";
}

if (isset($_GET['deleted'])) $pesan = "Anggota berhasil dihapus!";

// Ambil semua anggota
$anggota = $pdo->query("SELECT * FROM pengguna ORDER BY nama_lengkap")->fetchAll();

// Edit mode
$edit_data = null;
if (isset($_GET['edit'])) {
    $q = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna=?");
    $q->execute([intval($_GET['edit'])]);
    $edit_data = $q->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Anggota - Admin</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <img src="../assets/ukm_cendikia.png" alt="Logo" style="width:38px;height:38px;object-fit:contain;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <span style="display:none;font-size:1.1rem;">🗓️</span>
      </div>
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
      <a href="tambah_jadwal.php" class="nav-item">
        <span class="nav-icon">📅</span> Jadwal
      </a>
      <p class="nav-label">Master Data</p>
      <a href="kelola_anggota.php" class="nav-item active">
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
        <h2>Kelola Anggota</h2>
        <p>Tambah, edit, dan hapus data anggota UKM</p>
      </div>
    </header>

    <main class="page-content">
      <?php if ($pesan): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:24px;align-items:start;">

        <!-- FORM TAMBAH / EDIT -->
        <div class="card">
          <div class="card-header">
            <h3><?= $edit_data ? '✏️ Edit Anggota' : '➕ Tambah Anggota' ?></h3>
            <?php if ($edit_data): ?>
              <a href="kelola_anggota.php" class="btn btn-sm" style="background:var(--gray-light);color:var(--text-dark);border:1px solid var(--gray-mid);">Batal</a>
            <?php endif; ?>
          </div>
          <form method="POST">
            <input type="hidden" name="aksi" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
            <?php if ($edit_data): ?>
              <input type="hidden" name="id_pengguna" value="<?= $edit_data['id_pengguna'] ?>">
            <?php endif; ?>

            <div class="form-group">
              <label>NIM</label>
              <input type="text" name="nim" placeholder="Contoh: 2021001"
                value="<?= $edit_data ? htmlspecialchars($edit_data['nim']) : '' ?>" required>
            </div>
            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="nama_lengkap" placeholder="Nama lengkap anggota"
                value="<?= $edit_data ? htmlspecialchars($edit_data['nama_lengkap']) : '' ?>" required>
            </div>
            <div class="form-group">
              <label>Password <?= $edit_data ? '<span style="font-weight:400;color:var(--gray-text)">(kosongkan jika tidak diubah)</span>' : '' ?></label>
              <input type="password" name="password" placeholder="<?= $edit_data ? 'Isi untuk ubah password' : 'Buat password' ?>"
                <?= $edit_data ? '' : 'required' ?>>
            </div>
            <button type="submit" class="btn btn-blue" style="width:100%;justify-content:center;">
              <?= $edit_data ? '💾 Simpan Perubahan' : '➕ Tambah Anggota' ?>
            </button>
          </form>
        </div>

        <!-- TABEL ANGGOTA -->
        <div class="card">
          <div class="card-header">
            <h3>👥 Daftar Anggota (<?= count($anggota) ?>)</h3>
          </div>
          <div class="table-wrapper">
            <?php if (empty($anggota)): ?>
              <p style="text-align:center;padding:32px;color:var(--gray-text);">Belum ada anggota terdaftar.</p>
            <?php else: ?>
            <table>
              <thead>
                <tr><th>No</th><th>NIM</th><th>Nama Lengkap</th><th>Aksi</th></tr>
              </thead>
              <tbody>
                <?php foreach ($anggota as $i => $a): ?>
                <tr>
                  <td style="color:var(--gray-text);font-weight:600;"><?= $i+1 ?></td>
                  <td><code style="background:var(--blue-soft);padding:2px 8px;border-radius:4px;font-size:0.82rem;"><?= htmlspecialchars($a['nim']) ?></code></td>
                  <td><strong><?= htmlspecialchars($a['nama_lengkap']) ?></strong></td>
                  <td>
                    <a href="kelola_anggota.php?edit=<?= $a['id_pengguna'] ?>" class="action-link edit">Edit</a>
                    <a href="kelola_anggota.php?hapus=<?= $a['id_pengguna'] ?>" class="action-link delete"
                       onclick="return confirm('Hapus anggota <?= htmlspecialchars($a['nama_lengkap']) ?>?')">Hapus</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </main>
  </div>
</div>
</body>
</html>
